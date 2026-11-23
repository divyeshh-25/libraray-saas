<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsLibrary;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use BuildsLibrary, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_one_login_page_sends_each_role_to_its_own_home(): void
    {
        $tenant = $this->tenant();
        $branch = $this->branch($tenant);
        $owner = $this->owner($tenant);
        $student = $this->student($tenant, $branch);
        $staff = $this->staffWith($tenant, $branch, ['students.view']);

        foreach ([[$owner, 'dashboard'], [$staff, 'dashboard'], [$student->user, 'student.dashboard']] as [$user, $home]) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(route($home));
            $this->post(route('logout'));
        }
    }

    public function test_login_page_lists_demo_accounts_and_student_login_page_is_gone(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('owner@demolibrary.test')->assertSee('main.librarian@demolibrary.test')->assertSee('main.student001@demolibrary.test');
        $this->get('/student/login')->assertNotFound();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $tenant = $this->tenant();
        $owner = $this->owner($tenant);
        $this->post(route('login'), ['email' => $owner->email, 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
