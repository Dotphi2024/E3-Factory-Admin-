<form action="{{ route('master.participants.receive-registration-due-payment') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" tabindex="-1" id="receiveRegistrationDuePaymentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Registration Due Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group m-2">
                        <label for="name" class="form-label">Due Amount
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" id="due_amount" disabled>
                    </div>
                    <div class="form-group m-2">
                        <label for="amount" class="form-label">Amount
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" name="amount" id="amount" required>
                    </div>
                    <div class="form-group m-2">
                        <label for="transaction_id" class="form-label">Transaction ID </label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                            value="{{ old('transaction_id') }}" required>
                    </div>
                    <div class="form-group m-2">
                        <label for="payment_mode" class="form-label">Payment Mode </label>
                        <select name="payment_mode" id="payment_mode" class="form-select" data-placeholder="Select"
                            required>
                            <option label="Select"></option>
                            <option value="Gpay">Gpay</option>
                            <option value="Phonepe">Phonepe</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group m-2">
                        <label for="payment_image" class="form-label">Payment Image </label>
                        <input type="file" class="form-control" id="payment_image" name="payment_image">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="participant_id" id="participant_id">
                    <input type="hidden" name="batch_id" id="batch_id">

                    <input type="hidden" name="registration_fees_due" id="registration_fees_due">
                    <input type="hidden" name="registration_fees_paid" id="registration_fees_paid">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </div>
        </div>
    </div>
</form>
