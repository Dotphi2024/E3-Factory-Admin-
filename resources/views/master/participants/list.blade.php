@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Participants List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('participant add')
                <!-- <div class="col">
                    <button type="button" class="btn btn-secondary px-5" data-bs-toggle="modal"
                        data-bs-target="#importParticipantModal">Import</button>
                </div>
              <div>

                    <button type="button" id="exportBtn" class="btn btn-success px-5">Export</button>
                </div>
                <div class="col mt-3">
                    <button type="button" class="btn btn-warning px-5" data-bs-toggle="modal"
                        data-bs-target="#receivePaymentModal">Receive Payment</button>
                </div>

                <a href="{{ route('master.participants.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add New Participant</button>
                    </div>
                </a> -->


                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center w-100">
                        
                        <button type="button" class="btn btn-secondary px-4" data-bs-toggle="modal"
                            data-bs-target="#importParticipantModal">Import</button>
                            
                        <button type="button" id="exportBtn" class="btn btn-success px-4">Export</button>
                        
                        <button type="button" class="btn btn-warning px-4 text-dark" data-bs-toggle="modal"
                            data-bs-target="#receivePaymentModal">Receive Payment</button>

                        <a href="{{ route('master.participants.add') }}" class="ms-auto">
                            <button type="button" class="btn btn-primary px-4">Add New Participant</button>
                        </a>
                        
                </div>
            @endcan
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name </th>
                            <th>Current Batch</th>
                            <th>Total Batches Enrolled</th>
                            <th>Mobile</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Status</th>
                            <th>City</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($participants as $key => $participant)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('master.participants.view', $participant->id) }}">{{ $participant->first_name }}
                                        {{ $participant->last_name }}</a>
                                </td>
                                <td>
                                    @if ($participant->batch)
                                        <a
                                            href="{{ route('master.batches.view', $participant->batch) }}">{{ $participant->batch->name }}</a>
                                    @endif
                                </td>
                                <td>{{ $participant->participantBatches->count() }}</td>
                                <td>{{ $participant->mobile }}</td>
                                <td>{{ $participant->paid_amount }}</td>
                                <td>{{ $participant->due_amount }}</td>
                                <td>

                                    @if ($participant->is_active)
                                        <a
                                            href="{{ route('master.participants.update-active-status', $participant->id) }}"><span
                                                class="badge bg-success">Active</span></a>
                                    @else
                                        <a
                                            href="{{ route('master.participants.update-active-status', $participant->id) }}"><span
                                                class="badge bg-danger">Inactive</a>
                                    @endif
                                </td>
                                <td>{{ $participant->city }}</td>
                                <td>{{ $participant->addedBy ? $participant->addedBy->name : '' }}</td>
                                <td>{{ $participant->created_at->format('d-m-Y') }}</td>
                                <td>

                                    @can('participant edit')
                                        <a href="{{ route('master.participants.edit', $participant->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    @can('participant delete')
                                        <a href="{{ route('master.participants.delete', $participant->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-delete">
                                                <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                <line x1="18" y1="9" x2="12" y2="15"></line>
                                                <line x1="12" y1="9" x2="18" y2="15"></line>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    {{-- @can('participant edit')
                                        @if (!$participant->is_registration_fees_paid)
                                            <a href="{{ route('master.participants.add-registration-payment', $participant->id) }}"
                                                title="Add Registration Payment"><svg xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="feather feather-credit-card">
                                                    <rect x="1" y="4" width="22" height="16" rx="2"
                                                        ry="2">
                                                    </rect>
                                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                                </svg>
                                            </a>
                                        @endif
                                    @endcan --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Import Participant Modal --}}
    @include('master.participants.import-modal')
    {{-- End Import Participant Modal --}}

    {{-- Receive Payment Modal --}}
    <form action="{{ route('master.participants.receive-payment') }}" method="get">
        <div class="modal fade" tabindex="-1" id="receivePaymentModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Receive Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="participant_id" class="form-label">Participants </label>
                            <select name="participant_id" id="participant_id" class="single-select" required>
                                <option label="Select"></option>
                                @foreach ($participants as $participant)
                                    <option value="{{ $participant->id }}">{{ $participant->first_name }}
                                        {{ $participant->last_name }} ({{ $participant->mobile }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Go</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End Receive Payment Modal --}}
@endsection
@section('custom_js')
    <script>
        $(document).ready(function() {
            // Your existing select2 setup
            $('#participant_id').select2({
                placeholder: 'Select',
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true,
                dropdownParent: $('#receivePaymentModal')
            });

            // 1. Initialize your DataTable normally
            var table = $('#example').DataTable();

            // 2. Export ALL entries logic
            $('#exportBtn').on('click', function() {
                var csv = [];
                
                // Get header rows (excluding the last column 'Actions')
                var headers = [];
                $('#example th').each(function(index, item) {
                    if (index < 11) { // Stop before the 12th column (index 11)
                        headers.push('"' + $(item).text().trim().replace(/"/g, '""') + '"');
                    }
                });
                csv.push(headers.join(","));

                // Fetch ALL rows from DataTables internal cache memory
                // 'search: "applied"' ensures if a user filters data, it exports all filtered records instead of just page 1
                var allData = table.rows({ search: 'applied' }).nodes();

                $(allData).each(function(rowIndex, rowElement) {
                    var rowData = [];
                    var cells = $(rowElement).find('td');

                    cells.each(function(colIndex, cellElement) {
                        if (colIndex < 11) { // Skip the Actions column
                            // Clean up layout break tags and outer whitespace
                            var textData = $(cellElement).text().replace(/(\r\n|\n|\r)/gm, "").trim();
                            
                            // Escape double quotes inside data field values
                            textData = textData.replace(/"/g, '""');
                            
                            rowData.push('"' + textData + '"');
                        }
                    });
                    
                    csv.push(rowData.join(","));
                });

                // 3. Construct file down-stream compiler link
                var csvFile = new Blob([csv.join("\n")], {type: "text/csv;charset=utf-8;"});
                var downloadLink = document.createElement("a");
                var filename = 'all_participants_' + new Date().toISOString().slice(0,10) + '.csv';
                
                if (navigator.msSaveBlob) { 
                    navigator.msSaveBlob(csvFile, filename);
                } else {
                    downloadLink.download = filename;
                    downloadLink.href = window.URL.createObjectURL(csvFile);
                    downloadLink.style.display = "none";
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                }
            });
        });
    </script>
@endsection
@section('custom_js')
    <script>
        $(document).ready(function() {
            $('#participant_id').select2({
                placeholder: 'Select',
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true,
                dropdownParent: $('#receivePaymentModal')
            });

        });
    </script>
@endsection
