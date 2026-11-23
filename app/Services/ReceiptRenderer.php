<?php

namespace App\Services;

use App\Models\FeeTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReceiptRenderer
{
    private function load(FeeTransaction $transaction): FeeTransaction
    {
        return $transaction->load(['invoice.feeStructure', 'student.user.branch', 'student.user.tenant', 'receivedBy']);
    }

    /** Printable HTML receipt (browser print dialog). */
    public function view(FeeTransaction $transaction, string $pdfUrl): View
    {
        return view('receipts.document', ['tx' => $this->load($transaction), 'forPdf' => false, 'pdfUrl' => $pdfUrl]);
    }

    public function download(FeeTransaction $transaction): Response
    {
        $transaction = $this->load($transaction);

        return Pdf::loadView('receipts.document', ['tx' => $transaction, 'forPdf' => true, 'pdfUrl' => null])
            ->setPaper('a5', 'portrait')
            ->download($transaction->receipt_number.'.pdf');
    }
}
