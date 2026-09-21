@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Assignments of {{ $batch_schedule->batch ? $batch_schedule->batch->name : '' }} / Session
        Name-
        <a href="{{route('master.batches.view', $batch_schedule->batch_id)}}">{{ $batch_schedule->name }}</a></h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('batch add')
                <a href="{{ route('master.batches.add-session-assignment', $batch_schedule->id) }}">
                    <div class="col" style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add New Assignment</button>
                    </div>
                </a>
                <div style="display: ruby !important">

                    <a href="{{ route('master.batches.start-all-session-assignment', $batch_schedule->id) }}">
                        <div class="col" >
                            <button type="button" class="btn btn-primary px-5">Start All</button>
                        </div>
                    </a>
                    <a href="{{ route('master.batches.stop-all-session-assignment', $batch_schedule->id) }}">
                        <div class="col" >
                            <button type="button" class="btn btn-primary px-5">Stop All</button>
                        </div>
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
                            <th>Question </th>
                            <th>Option Type</th>
                            <th>Options with Marks</th>
                            <th>Started</th>
                            <th>Enable File Upload</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batch_schedule->assignments as $key => $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $assignment->question }}</td>
                                <td>{{ $assignment->option_type }}</td>
                                <td>
                                    @if ($assignment->option_type == 'options')
                                        @foreach ($assignment->options as $assignment_option)
                                            {{ $assignment_option->option }} {{ $assignment_option->mark }}
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>{{ $assignment->is_started ? 'Yes' : 'No' }}</td>
                                <td>{{ $assignment->is_file_upload ? 'Yes' : 'No' }}</td>
                                <td>{{ $assignment->created_at->format('d-m-Y') }}</td>
                                <td>

                                    @can('batch edit')
                                        <a href="{{ route('master.batches.edit-session-assignment', $assignment->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                        <a href="{{ route('master.batches.start-stop-session-assignment', $assignment->id) }}"
                                            title="{{ $assignment->is_started ? 'Stop' : 'Start' }} Assignment">
                                            @if ($assignment->is_started)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-stop-circle">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <rect x="9" y="9" width="6" height="6"></rect>
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-play">
                                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                                </svg>
                                            @endif
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    @can('batch delete')
                                        <a href="{{ route('master.batches.delete-session-assignment', $assignment->id) }}"><svg
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
@endsection
