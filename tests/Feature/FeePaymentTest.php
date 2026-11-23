<?php

namespace Tests\Feature;

use App\Models\FeeInvoice;
use App\Models\FeeTransaction;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLibrary;
use Tests\TestCase;

class FeePaymentTest extends TestCase
{
    use BuildsLibrary, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** @return array{0: User, 1: Student, 2: array<string, mixed>} */
    private function setUpEnrollable(int $fee = 1200): array
    {
        $tenant = $this->tenant();
        $branch = $this->branch($tenant);
        $owner = $this->owner($tenant, $branch);
        $student = $this->student($tenant, $branch);
        $shift = $this->shift($branch);
        $plan = $this->feePlan($branch, $shift, $fee);

        return [$owner, $student, $this->enrollPayload($student, $branch, $shift, $plan)];
    }

    public function test_enrolling_a_student_generates_an_invoice_for_the_full_fee(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1200);

        $this->actingAs($owner)->post(route('enrollments.store'), $payload)->assertRedirect(route('students.index'));

        $invoice = FeeInvoice::firstOrFail();
        $this->assertSame($student->id, $invoice->student_id);
        $this->assertSame(StudentEnrollment::first()->id, $invoice->enrollment_id);
        $this->assertSame('1200.00', $invoice->final_amount);
        $this->assertSame('0.00', $invoice->paid_amount);
        $this->assertSame('1200.00', $invoice->balance_amount);
        $this->assertSame('unpaid', $invoice->status);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertDatabaseCount('fee_transactions', 0);
    }

    public function test_a_partial_payment_at_enrollment_saves_a_transaction_and_receipt(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1200);

        $this->actingAs($owner)->postJson(route('enrollments.store'), $payload + [
            'initial_payment' => 500, 'payment_method' => 'upi', 'payment_reference' => 'UPI123',
        ])->assertOk()->assertJsonStructure(['receipt_url']);

        $tx = FeeTransaction::firstOrFail();
        $this->assertSame('500.00', $tx->amount);
        $this->assertSame('upi', $tx->payment_method);
        $this->assertSame('success', $tx->status);
        $this->assertSame('UPI123', $tx->reference_number);
        $this->assertSame($owner->id, $tx->received_by);
        $this->assertStringStartsWith('RCT-', $tx->receipt_number);

        $invoice = FeeInvoice::firstOrFail();
        $this->assertSame('500.00', $invoice->paid_amount);
        $this->assertSame('700.00', $invoice->balance_amount);
        $this->assertSame('partial', $invoice->status);
    }

    public function test_a_payment_larger_than_the_fee_rolls_back_the_whole_enrollment(): void
    {
        [$owner, , $payload] = $this->setUpEnrollable(1200);

        $this->actingAs($owner)->postJson(route('enrollments.store'), $payload + [
            'initial_payment' => 5000, 'payment_method' => 'cash',
        ])->assertStatus(422)->assertJsonValidationErrors('amount');

        $this->assertDatabaseCount('student_enrollments', 0);
        $this->assertDatabaseCount('fee_invoices', 0);
        $this->assertDatabaseCount('fee_transactions', 0);
    }

    public function test_enrollment_payment_input_is_validated(): void
    {
        [$owner, , $payload] = $this->setUpEnrollable();

        $this->actingAs($owner)->postJson(route('enrollments.store'), $payload + ['initial_payment' => -5, 'payment_method' => 'cash'])
            ->assertStatus(422)->assertJsonValidationErrors('initial_payment');
        $this->actingAs($owner)->postJson(route('enrollments.store'), $payload + ['initial_payment' => 100, 'payment_method' => 'barter'])
            ->assertStatus(422)->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseCount('student_enrollments', 0);
    }

    public function test_installments_update_the_balance_and_each_get_a_unique_receipt(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1000);
        $this->actingAs($owner)->post(route('enrollments.store'), $payload);
        $url = route('students.collect-fee', $student->id);

        $first = $this->actingAs($owner)->postJson($url, ['amount' => 400, 'paid_on' => now()->toDateString(), 'payment_method' => 'cash'])->assertOk();
        $this->assertSame('600.00', FeeInvoice::first()->balance_amount);
        $this->assertSame('partial', FeeInvoice::first()->status);

        $second = $this->actingAs($owner)->postJson($url, ['amount' => 600, 'paid_on' => now()->toDateString(), 'payment_method' => 'card'])->assertOk();

        $invoice = FeeInvoice::first();
        $this->assertSame('1000.00', $invoice->paid_amount);
        $this->assertSame('0.00', $invoice->balance_amount);
        $this->assertSame('paid', $invoice->status);
        $this->assertDatabaseCount('fee_invoices', 1);
        $this->assertDatabaseCount('fee_transactions', 2);
        $this->assertNotSame($first['receipt_url'], $second['receipt_url']);
        $this->assertCount(2, FeeTransaction::pluck('receipt_number')->unique());
    }

    public function test_overpaying_or_paying_a_settled_invoice_is_rejected(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1000);
        $this->actingAs($owner)->post(route('enrollments.store'), $payload);
        $url = route('students.collect-fee', $student->id);
        $body = ['paid_on' => now()->toDateString(), 'payment_method' => 'cash'];

        $this->actingAs($owner)->postJson($url, $body + ['amount' => 1000.01])->assertStatus(422)->assertJsonValidationErrors('amount');
        $this->actingAs($owner)->postJson($url, $body + ['amount' => 1000])->assertOk();
        $this->actingAs($owner)->postJson($url, $body + ['amount' => 1])->assertOk(); // no invoice left: raises a one-off invoice

        $this->assertSame('paid', FeeInvoice::orderBy('id')->first()->status);
        $this->assertSame('1000.00', FeeInvoice::orderBy('id')->first()->paid_amount);
    }

    public function test_collecting_a_fee_with_no_open_invoice_raises_a_one_off_invoice_and_receipt(): void
    {
        [$owner, $student] = $this->setUpEnrollable();

        $this->actingAs($owner)->postJson(route('students.collect-fee', $student->id), [
            'amount' => 250, 'paid_on' => now()->toDateString(), 'notes' => 'Locker fee',
        ])->assertOk();

        $invoice = FeeInvoice::firstOrFail();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('250.00', $invoice->final_amount);
        $this->assertSame('0.00', $invoice->balance_amount);
        $this->assertSame('cash', FeeTransaction::first()->payment_method);
    }

    public function test_collect_fee_validates_input(): void
    {
        [$owner, $student] = $this->setUpEnrollable();
        $url = route('students.collect-fee', $student->id);

        $this->actingAs($owner)->postJson($url, ['amount' => 0, 'paid_on' => 'nope'])->assertStatus(422)->assertJsonValidationErrors(['amount', 'paid_on']);
        $this->actingAs($owner)->postJson($url, ['amount' => 10, 'paid_on' => now()->toDateString(), 'payment_method' => 'gold'])->assertStatus(422)->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseCount('fee_transactions', 0);
    }

    public function test_receipt_can_be_viewed_and_downloaded_as_pdf(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1000);
        $this->actingAs($owner)->post(route('enrollments.store'), $payload);
        $this->actingAs($owner)->postJson(route('students.collect-fee', $student->id), ['amount' => 300, 'paid_on' => now()->toDateString()])->assertOk();
        $tx = FeeTransaction::firstOrFail();

        $this->actingAs($owner)->get(route('receipts.show', $tx->id))
            ->assertOk()->assertSee($tx->receipt_number)->assertSee('PARTIAL PAYMENT')->assertSee('700.00')->assertSee('window.print()', false);

        $pdf = $this->actingAs($owner)->get(route('receipts.pdf', $tx->id))->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringContainsString($tx->receipt_number.'.pdf', $pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_another_tenant_cannot_open_a_receipt_or_pay_for_the_student(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1000);
        $this->actingAs($owner)->post(route('enrollments.store'), $payload);
        $this->actingAs($owner)->postJson(route('students.collect-fee', $student->id), ['amount' => 100, 'paid_on' => now()->toDateString()]);
        $tx = FeeTransaction::firstOrFail();

        $tenantB = $this->tenant('Lib B');
        $intruder = $this->owner($tenantB, $this->branch($tenantB, 'B'));

        $this->actingAs($intruder)->get(route('receipts.show', $tx->id))->assertNotFound();
        $this->actingAs($intruder)->get(route('receipts.pdf', $tx->id))->assertNotFound();
        $this->actingAs($intruder)->postJson(route('students.collect-fee', $student->id), ['amount' => 100, 'paid_on' => now()->toDateString()])->assertNotFound();
        $this->assertDatabaseCount('fee_transactions', 1);
    }

    public function test_staff_permissions_gate_enrolling_and_collecting_and_receipts(): void
    {
        $tenant = $this->tenant();
        $branch = $this->branch($tenant);
        $student = $this->student($tenant, $branch);
        $shift = $this->shift($branch);
        $plan = $this->feePlan($branch, $shift);

        $viewer = $this->staffWith($tenant, $branch, ['students.view']);
        $this->actingAs($viewer)->postJson(route('enrollments.store'), $this->enrollPayload($student, $branch, $shift, $plan))->assertForbidden();
        $this->actingAs($viewer)->postJson(route('students.collect-fee', $student->id), ['amount' => 10, 'paid_on' => now()->toDateString()])->assertForbidden();

        $clerk = $this->staffWith($tenant, $branch, ['students.view', 'students.enroll', 'students.update']);
        $this->actingAs($clerk)->postJson(route('enrollments.store'), $this->enrollPayload($student, $branch, $shift, $plan, ['initial_payment' => 200, 'payment_method' => 'cash']))->assertOk();
        $tx = FeeTransaction::firstOrFail();
        $this->assertSame($clerk->id, $tx->received_by);
        $this->actingAs($clerk)->get(route('receipts.show', $tx->id))->assertOk();

        $noAccess = $this->staffWith($tenant, $branch, ['halls.view']);
        $this->actingAs($noAccess)->get(route('receipts.show', $tx->id))->assertForbidden();
    }

    public function test_student_sees_payment_history_and_only_their_own_receipts(): void
    {
        [$owner, $student, $payload] = $this->setUpEnrollable(1000);
        $this->actingAs($owner)->post(route('enrollments.store'), $payload + ['initial_payment' => 250, 'payment_method' => 'cash']);
        $tx = FeeTransaction::firstOrFail();

        $other = $this->student($student->user->tenant, $student->user->branch, 'STU2');
        $this->actingAs($owner)->postJson(route('students.collect-fee', $other->id), ['amount' => 99, 'paid_on' => now()->toDateString()])->assertOk();
        $otherTx = FeeTransaction::where('student_id', $other->id)->firstOrFail();

        $this->actingAs($student->user)->get(route('student.dashboard'))
            ->assertOk()->assertSee('Payment history')->assertSee($tx->receipt_number)->assertSee('750.00')->assertDontSee($otherTx->receipt_number);

        $this->actingAs($student->user)->get(route('student.receipts.show', $tx->id))->assertOk()->assertSee($tx->receipt_number);
        $this->assertSame('application/pdf', $this->actingAs($student->user)->get(route('student.receipts.pdf', $tx->id))->assertOk()->headers->get('content-type'));
        $this->actingAs($student->user)->get(route('student.receipts.show', $otherTx->id))->assertNotFound();
        $this->actingAs($student->user)->get(route('receipts.show', $tx->id))->assertForbidden();
    }
}
