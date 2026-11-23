<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToViewer;
use App\Models\FeeTransaction;
use App\Services\ReceiptRenderer;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Receipts for the owner/staff area. A receipt is just a successful
 * fee_transactions row; scoping is by the paying student's tenant/branch.
 */
class ReceiptController extends Controller
{
    use ScopesToViewer;

    public function show(ReceiptRenderer $renderer, int $id): View
    {
        return $renderer->view($this->find($id), route('receipts.pdf', $id));
    }

    public function pdf(ReceiptRenderer $renderer, int $id): Response
    {
        return $renderer->download($this->find($id));
    }

    private function find(int $id): FeeTransaction
    {
        return $this->scopeThroughStudentUser(FeeTransaction::query())
            ->where('status', 'success')
            ->findOrFail($id);
    }
}
