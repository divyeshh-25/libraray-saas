<div id="collect-fee-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/40">
    <div class="card w-full max-w-sm p-5 shadow-xl">
        <h3 class="font-semibold text-[15px]">Collect fee — <span id="collect-fee-student-name"></span></h3>
        <form class="mt-4 space-y-3">
            @csrf
            <div data-form-errors></div>
            <input type="hidden" name="fee_structure_id">
            <label class="block">
                <span class="text-[12.5px] text-muted">Amount (₹)</span>
                <input type="number" name="amount" class="input w-full mt-1" min="0.01" step="0.01" required>
            </label>
            <label class="block">
                <span class="text-[12.5px] text-muted">Payment date</span>
                <input type="date" name="paid_on" class="input w-full mt-1" value="{{ now()->toDateString() }}" required>
            </label>
            <label class="block">
                <span class="text-[12.5px] text-muted">Payment method</span>
                <select name="payment_method" class="input w-full mt-1">
                    @foreach (\App\Models\FeeTransaction::METHODS as $m)
                        <option value="{{ $m }}">{{ ucwords(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-[12.5px] text-muted">Reference no. (optional)</span>
                <input type="text" name="reference_number" class="input w-full mt-1" maxlength="150" placeholder="UPI / cheque / bank reference">
            </label>
            <label class="block">
                <span class="text-[12.5px] text-muted">Notes (optional)</span>
                <input type="text" name="notes" class="input w-full mt-1" placeholder="e.g. Cash, UPI reference...">
            </label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn-primary">Collect fee</button>
            </div>
        </form>
    </div>
</div>
