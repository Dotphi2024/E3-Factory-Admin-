@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Group Message List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('guest-homepage add')
                <a href="javascript:void(0)">
                    <div class="col"style="float: right">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addGroupMessageModal"
                            class="btn btn-primary px-5">Add New Message to Group</button>
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
                            <th>Group Name </th>
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
                                <td>{{ $group_message->batchGroup ? $group_message->batchGroup->name : '' }}</td>
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
    <form action="{{ route('master.messages.store-group-messages') }}" method="post" enctype="multipart/form-data"
        id="start-registration-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="addGroupMessageModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Message to Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="group_id" class="form-label">Select Group </label>
                            <select name="group_id" id="group_id" class="form-control" required>
                                <option label="Select"></option>
                                @foreach ($batch_groups as $batch_group)
                                    <option value="{{ $batch_group->id }}"
                                        {{ old('group_id') == $batch_group->id ? 'selected' : '' }}>{{ $batch_group->name }}</option>
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
            $('#group_id').select2({
                placeholder: 'Select Group',
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true,
                dropdownParent: $('#addGroupMessageModal')
            })
        });
    </script>
@endsection
