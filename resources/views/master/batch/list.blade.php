@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Batch List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('batch add')
                <a href="{{ route('master.batches.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add New Batch</button>
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
                            <th>Name </th>
                            <th>Program</th>
                            <th>Number of Session</th>
                            <th>Fee One Time</th>
                            <th>Incidental Charge</th>
                            <th>Is Active</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batches as $key => $batch)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{route('master.batches.view',$batch->id)}}">{{ $batch->name }}</a>
                                </td>
                                <td>{{ $batch->course ? $batch->course->name : '' }}</td>
                                <td>{{ $batch->number_of_sessions }}</td>
                                <td>{{ $batch->registration_fee }}</td>
                                <td>{{ $batch->fee_per_session }}</td>
                                <td>

                                    @if ($batch->is_active)
                                        <a href="{{ route('master.batches.update-active-status', $batch->id) }}"><span
                                                class="badge bg-success">Active</span></a>
                                    @else
                                        <a href="{{ route('master.batches.update-active-status', $batch->id) }}"><span
                                                class="badge bg-danger">Inactive</a>
                                    @endif
                                </td>
                                <td>{{ $batch->addedBy ? $batch->addedBy->name : '' }}</td>
                                <td>{{ $batch->created_at->format('d-m-Y') }}</td>
                                <td>

                                    @can('batch edit')
                                        <a href="{{ route('master.batches.edit', $batch->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    @can('batch delete')
                                        <a href="{{ route('master.batches.delete', $batch->id) }}"><svg
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
                                    {{-- @can('batch edit')
                                        <a href="{{ route('master.batches.update-coach-registration-status', $batch->id) }}">
                                            @if ($batch->start_coach_registration)
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-minus-square">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"
                                                        ry="2"></rect>
                                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-plus-square">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"
                                                        ry="2"></rect>
                                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                                </svg>
                                            @endif
                                        </a>
                                    @endcan --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
