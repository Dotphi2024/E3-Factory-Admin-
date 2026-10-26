<form action="{{ route('master.participants.import') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" tabindex="-1" id="importParticipantModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Participants</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group m-2">
                        <label for="name" class="form-label">Select File
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <a href="{{ asset('sample-file/participant-sample.xlsx') }}" class="small">Download Sample File</a>
                        </div>
                    </div>
                    <div class="form-check m-2 mt-3">
                        <input class="form-check-input" type="checkbox" name="send_whatsapp" id="send_whatsapp" value="1">
                        <label class="form-check-label" for="send_whatsapp">
                            Send WhatsApp confirmation message to imported participants
                        </label>
                        <div class="form-text text-muted small">
                            Leave unchecked for fast import without triggering external WhatsApp API calls.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </div>
    </div>
</form>
