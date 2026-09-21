@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Participant Message List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('guest-homepage add')
                <a href="javascript:void(0)">
                    <div class="col"style="float: right">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addParticipantMessageModal"
                            class="btn btn-primary px-5">Add New Message to Participant</button>
                    </div>
                </a>
            @endcan
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Participant Name </th>
                            <th>Coach Name</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Is Deleted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group_messages as $key => $group_message)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $group_message->participant ? $group_message->participant->first_name . ' ' . $group_message->participant->last_name : '' }}</td>
                                <td>{{ $group_message->coach ? $group_message->coach->first_name . ' ' . $group_message->coach->last_name : '' }}
                                </td>
                                <td>{{ $group_message->message }}</td>
                                <td>
                                    <textarea class="form-control" disabled>{{ $group_message->description }}</textarea>
                                </td>
                                <td>{{ $group_message->created_at->format('d-m-Y') }}</td>
                                <td>{{ $group_message->deleted_at ? 'Yes' : 'No' }}</td>
                                <td>
                                    @can('messages delete')
                                        <a href="{{ route('master.messages.delete-messages', $group_message->id) }}"><svg
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Add Message to Batch Modal --}}
    <form action="{{ route('master.messages.store-participant-messages') }}" method="post" enctype="multipart/form-data"
        id="start-registration-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="addParticipantMessageModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Message to Participant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="participant_id" class="form-label">Select Participant </label>
                            <select name="participant_id" id="participant_id" required>
                                <option label="Select"></option>
                                @foreach ($participants as $participant)
                                    <option value="{{ $participant->id }}"
                                        {{ old('participant_id') == $participant->id ? 'selected' : '' }}>{{ $participant->first_name . ' ' . $participant->last_name }} ({{ $participant->mobile }})</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="form-group m-2">
                            <label for="message" class="form-label">Title </label>
                            <input type="text" name="message" id="message" class="form-control"
                                value="{{ old('message') }}" required>

                        </div>
                        <div class="form-group m-2">
                            <label for="message" class="form-label">Description </label>
                            <textarea name="description" id="description" class="form-control" required>{{ old('description') }}</textarea>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End  --}}
@endsection
@section('custom_js')
    <script>
        $(document).ready(function() {
            $('#participant_id').select2({
                placeholder: 'Select Participant',
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true,
                dropdownParent: $('#addParticipantMessageModal')
            })
        });
    </script>
@endsection
