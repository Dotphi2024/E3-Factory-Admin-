@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Leads List</h6>
    <hr />
    <div class="card">
        <div class="card-body">

            @can('recommendations add')
                <a href="{{ route('master.recommendations.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add Lead</button>
                    </div>
                </a>
            @endcan
            <div class="row">
                <div class="col-6">

                    <form action="" class="d-flex" method="get">
                        <select name="status" id="staus" class="form-select">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pending</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Completed</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </div>
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Lead Name </th>
                            <th>Lead Status </th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Log</th>
                            <th>Completed Follow Up Log</th>
                            <th>Follow Up Status</th>
                            <th>Created At</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($follow_ups as $key => $follow_up)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $follow_up->recommendation ? $follow_up->recommendation->name : '' }}</td>
                                <td>{{ $follow_up->recommendation ? $follow_up->recommendation->follow_up : '' }}</td>

                                <td>{{ $follow_up->date }}</td>
                                <td>{{ $follow_up->time ? date('h:i A', strtotime($follow_up->time)) : '' }}</td>
                                <td>
                                    <textarea class="form-control" readonly>{{ $follow_up->log }}</textarea>
                                </td>
                                <td>
                                    <textarea class="form-control" readonly>{{ $follow_up->completed_follow_up_log }}</textarea>
                                </td>
                                <td>{{ $follow_up->is_follow_up_completed ? 'Completed' : 'Pending' }}</td>
                                <td>{{ $follow_up->created_at->format('d-m-Y') }}</td>
                                <td>{{ $follow_up->addedBy ? $follow_up->addedBy->name : '' }}</td>
                                <td>

                                    @can('recommendations edit')
                                        @if ($follow_up->recommendation && $follow_up->recommendation->follow_up == 'Pending')
                                            <a href="{{ route('master.recommendations.update-follow-up-status', $follow_up->recommendation->id) }}"
                                                title="Mark Lead As Completed">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-check">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </a>
                                            &nbsp;&nbsp;
                                        @endif
                                        @if (!$follow_up->is_follow_up_completed)
                                            <a href="javascript:void(0)"
                                                data-url="{{ route('master.recommendations.mark-follow-up-complete', $follow_up->id) }}"
                                                title="Mark Follow Up As Completed" class="follow-up-completed-btn"
                                                data-bs-toggle="modal" data-bs-target="#add-completed-follow-up-log-modal"
                                                data-id="{{ $follow_up->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-check-square">
                                                    <polyline points="9 11 12 14 22 4"></polyline>
                                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                                </svg>
                                            </a>
                                            &nbsp;&nbsp;
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Add Completed Follow Up Log Modal --}}
    <form action="" method="post" id="add-completed-follow-up-log-form">

        @csrf

        <div class="modal fade" tabindex="-1" id="add-completed-follow-up-log-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Completed Follow Up Log</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="completed_follow_up_log" class="form-label">Completed Follow Up Log </label>
                            <textarea name="completed_follow_up_log" class="form-control" id="completed_follow_up_log" required>{{ old('completed_follow_up_log') }}</textarea>

                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End Add Completed Follow Up Log Modal --}}
@endsection
@section('custom_js')
    <script>
        $(document).on('click', '.follow-up-completed-btn', function() {
            var url = $(this).data('url');
            $('#add-completed-follow-up-log-form').prop('action', url);
        })
    </script>
@endsection
