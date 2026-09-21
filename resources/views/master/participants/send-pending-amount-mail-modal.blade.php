<form action="{{ route('master.participants.send-pending-amount-mail') }}" method="post">
    @csrf
    <div class="modal fade" tabindex="-1" id="sendPendingAmountMailModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Pending Payment Mail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group m-2">
                        <label for="mail_recipient_email" class="form-label">Recipient Email
                            <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" name="recipient_email" id="mail_recipient_email" required readonly>
                    </div>
                    <div class="form-group m-2">
                        <label for="mail_pending_amount" class="form-label">Pending Amount</label>
                        <input type="text" class="form-control" id="mail_pending_amount" readonly disabled>
                    </div>
                    <div class="form-group m-2">
                        <label for="mail_subject" class="form-label">Subject
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="subject" id="mail_subject" required>
                    </div>
                    <div class="form-group m-2">
                        <label for="mail_message" class="form-label">Message
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="message" id="mail_message" rows="6" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="participant_id" id="mail_participant_id">
                    <input type="hidden" name="batch_id" id="mail_batch_id">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Mail</button>
                </div>
            </div>
        </div>
    </div>
</form>
