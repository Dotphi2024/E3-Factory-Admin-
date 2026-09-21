<form action="{{ route('master.participants.send-whatsapp-message') }}" method="post">
    @csrf
    <div class="modal fade" tabindex="-1" id="sendWhatsAppModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #25D366;">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-whatsapp me-2"></i> Send WhatsApp Reminder
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group m-2">
                        <label for="wa_mobile" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile" id="wa_mobile" readonly>
                    </div>
                    <div class="form-group m-2">
                        <label for="wa_pending_amount" class="form-label">Pending Amount</label>
                        <input type="text" class="form-control" id="wa_pending_amount" readonly disabled>
                    </div>
                    <div class="form-group m-2">
                        <label for="wa_message" class="form-label">Message
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="message" id="wa_message" rows="7" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="participant_id" id="wa_participant_id">
                    <input type="hidden" name="batch_id" id="wa_batch_id">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-whatsapp me-1"></i> Send on WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
