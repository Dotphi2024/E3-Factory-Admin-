@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Session List Of {{ $batch->name }}</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Participant
                        <a href="{{ route('master.participants.view', $participant->id) }}">{{ $participant->first_name }}
                            {{ $participant->last_name }}</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />
    <div class="card">
        <div class="card-body">
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name </th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batch->schedules as $key => $batch_schedule)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $batch_schedule->name }}</td>
                                <td>{{ $batch_schedule->date }}</td>
                                <td>{{ $batch_schedule->is_session_completed == 1 ? 'Completed' : ($batch_schedule->is_session_completed == 2 ? 'Active' : 'Pending') }}
                                </td>
                                <td>{{ $batch_schedule->created_at->format('d-m-Y') }}</td>
                                <td>

                                    @can('participant view')
                                        <a href="{{ route('master.participants.view-assignments', ['participant_id'=>$participant->id,'batch_schedule_id'=>$batch_schedule->id]) }}" title="View Assignments">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
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
