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
                        <a href="{{ asset('sample-file/participant-sample.xlsx') }}">Download Sample</a>
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
