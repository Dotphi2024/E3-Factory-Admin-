@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Batch</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Batch Details - {{ $batch->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary">Actions</button>
                <button type="button"
                    class="btn btn-outline-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                    {{-- <a class="dropdown-item"
                        href="javascript:;">Action</a> --}}
                    <a class="dropdown-item" href="{{ route('master.batches.edit', $batch->id) }}">Edit</a>
                    {{-- <a class="dropdown-item" href="javascript:;">Something else here</a>
                    <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated link</a> --}}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-9">

            {{-- Participants --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Participants</h4><br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>
                                    {{-- <th>Batch</th> --}}
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
                                @foreach ($batch->batchParticipants as $key => $participant)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                                        {{-- <td>{{ $participant->batch ? $participant->batch->name : '' }}</td> --}}
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
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-edit-2">
                                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                        </path>
                                                    </svg>
                                                </a>
                                                &nbsp;&nbsp;
                                            @endcan
                                            @can('participant delete')
                                                <a href="{{ route('master.participants.delete', $participant->id) }}"><svg
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-delete">
                                                        <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                        <line x1="18" y1="9" x2="12" y2="15">
                                                        </line>
                                                        <line x1="12" y1="9" x2="18" y2="15">
                                                        </line>
                                                    </svg>
                                                </a>
                                                &nbsp;&nbsp;
                                            @endcan
                                            @can('participant edit')
                                                @if (!$participant->pivot->is_registration_fees_paid)
                                                    <a href="{{ route('master.participants.add-registration-payment', $participant->pivot->id) }}"
                                                        title="Add Registration Payment"><svg xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-credit-card">
                                                            <rect x="1" y="4" width="22" height="16" rx="2"
                                                                ry="2">
                                                            </rect>
                                                            <line x1="1" y1="10" x2="23" y2="10">
                                                            </line>
                                                        </svg>
                                                    </a>
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
            {{-- End Participants --}}

            {{-- Groups --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Groups</h4><br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>
                                    <th>Coach</th>
                                    <th>Total Participants</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->groups as $key => $batch_group)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $batch_group->name }}</td>
                                        <td>{{ $batch_group->coach ? $batch_group->coach->first_name . ' ' . $batch_group->coach->last_name : '' }}
                                        </td>
                                        <td>{{ $batch_group->batchGroupParticipants->count() }}</td>
                                        <td>{{ $batch_group->addedBy ? $batch_group->addedBy->name : '' }}</td>
                                        <td>{{ $batch_group->created_at->format('d-m-Y') }}</td>
                                        <td>

                                            @can('batch edit')
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#add-group-modal" data-batch-group="{{ $batch_group }}"
                                                    data-url="{{ route('master.batches.update-group', $batch_group->id) }}"
                                                    class="edit-batch-group-btn"><svg xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" class="feather feather-edit-2">
                                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                        </path>
                                                    </svg>
                                                </a>
                                                &nbsp;&nbsp;
                                            @endcan
                                            @can('batch delete')
                                                <a href="{{ route('master.batches.delete-group', $batch_group->id) }}"><svg
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-delete">
                                                        <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                        <line x1="18" y1="9" x2="12" y2="15">
                                                        </line>
                                                        <line x1="12" y1="9" x2="18" y2="15">
                                                        </line>
                                                    </svg>
                                                </a>
                                                &nbsp;&nbsp;
                                            @endcan
                                            @can('batch view')
                                                <a href="javascript:void(0)" class="view-group-participants-btn"
                                                    data-batch-group="{{ $batch_group }}"
                                                    data-batch-group-participants = "{{ $batch_group->batchGroupParticipants }}"
                                                    data-bs-toggle="modal" data-bs-target="#view-group-participants-modal"
                                                    title="View Participants">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-eye">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- End --}}

            {{-- Head Coaches --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Head Coaches</h4><br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>
                                    <th>Batch</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>City</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->coaches as $key => $participant)
                                    @if ($participant->pivot->is_head_coach)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                                            <td>{{ $participant->batch ? $participant->batch->name : '' }}</td>
                                            <td>{{ $participant->mobile }}</td>

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
                                            <td>{{ $participant->added_by_user ? $participant->added_by_user->name : '' }}
                                            </td>
                                            <td>{{ $participant->created_at->format('d-m-Y') }}</td>
                                            <td>

                                                @can('participant edit')
                                                    <a href="{{ route('master.participants.edit', $participant->id) }}"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-edit-2">
                                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;

                                                    <a href="{{ route('master.batches.update-head-coach-status', $participant->pivot->id) }}"
                                                        title="remove-from-head-coach">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-user-x">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="8.5" cy="7" r="4"></circle>
                                                            <line x1="18" y1="8" x2="23"
                                                                y2="13">
                                                            </line>
                                                            <line x1="23" y1="8" x2="18"
                                                                y2="13">
                                                            </line>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endcan
                                                @can('participant delete')
                                                    <a href="{{ route('master.participants.delete', $participant->id) }}"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-delete">
                                                            <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                            <line x1="18" y1="9" x2="12"
                                                                y2="15">
                                                            </line>
                                                            <line x1="12" y1="9" x2="18"
                                                                y2="15">
                                                            </line>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endcan
                                                @can('participant edit')
                                                    {{-- @if (!$participant->is_registration_fees_paid)
                                                        <a href="{{ route('master.participants.add-registration-payment', $participant->id) }}"
                                                            title="Add Registration Payment"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-credit-card">
                                                                <rect x="1" y="4" width="22" height="16"
                                                                    rx="2" ry="2">
                                                                </rect>
                                                                <line x1="1" y1="10" x2="23"
                                                                    y2="10">
                                                                </line>
                                                            </svg>
                                                        </a>
                                                    @endif --}}
                                                @endcan
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- End Head Coaches --}}

            {{-- Coaches --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Coaches</h4><br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>
                                    <th>Batch</th>
                                    <th>Mobile</th>

                                    <th>Status</th>
                                    <th>City</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->coaches as $key => $participant)
                                    @if (!$participant->pivot->is_head_coach)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                                            <td>{{ $participant->batch ? $participant->batch->name : '' }}</td>
                                            <td>{{ $participant->mobile }}</td>

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
                                            <td>{{ $participant->added_by_user ? $participant->added_by_user->name : '' }}
                                            </td>
                                            <td>{{ $participant->created_at->format('d-m-Y') }}</td>
                                            <td>

                                                @can('participant edit')
                                                    <a href="{{ route('master.participants.edit', $participant->id) }}"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-edit-2">
                                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;

                                                    <a href="{{ route('master.batches.update-head-coach-status', $participant->pivot->id) }}"
                                                        title="make-head-coach">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-user-check">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="8.5" cy="7" r="4"></circle>
                                                            <polyline points="17 11 19 13 23 9"></polyline>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endcan
                                                @can('participant delete')
                                                    <a href="{{ route('master.participants.delete', $participant->id) }}"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-delete">
                                                            <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                            <line x1="18" y1="9" x2="12"
                                                                y2="15">
                                                            </line>
                                                            <line x1="12" y1="9" x2="18"
                                                                y2="15">
                                                            </line>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endcan
                                                @can('participant edit')
                                                    {{-- @if (!$participant->is_registration_fees_paid)
                                                        <a href="{{ route('master.participants.add-registration-payment', $participant->id) }}"
                                                            title="Add Registration Payment"><svg
                                                                xmlns="http://www.w3.org/2000/svg" width="24"
                                                                height="24" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                stroke-linejoin="round" class="feather feather-credit-card">
                                                                <rect x="1" y="4" width="22" height="16"
                                                                    rx="2" ry="2">
                                                                </rect>
                                                                <line x1="1" y1="10" x2="23"
                                                                    y2="10">
                                                                </line>
                                                            </svg>
                                                        </a>
                                                    @endif --}}
                                                @endcan
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- End Coaches --}}


            {{-- Schedules --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Schedules</h4><br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Session Name </th>
                                    <th>Date</th>
                                    <th>Amount</th>

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
                                        <td>{{ $batch_schedule->amount }}</td>
                                        <td>{{ $batch_schedule->is_session_completed == 1 ? 'Completed' : ($batch_schedule->is_session_completed == 2 ? 'Active' : 'Pending') }}
                                        </td>
                                        <td>{{ $batch_schedule->created_at->format('d-m-Y') }}</td>

                                        <td>
                                            @can('batch edit')
                                                @if ($batch_schedule->is_session_completed == 0)
                                                    <a href="javascript:void(0)" data-bs-toggle="modal"
                                                        data-bs-target="#edit-schedule-modal"
                                                        data-batch-schedule="{{ $batch_schedule }}"
                                                        data-url="{{ route('master.batches.update-batch-schedule', $batch_schedule->id) }}"
                                                        class="edit-batch-schedule-btn"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-edit-2">
                                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                            </path>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endif
                                                @if (
                                                    ($batch_schedule->is_session_completed == 0 || $batch_schedule->is_session_completed == 2) &&
                                                        !$batch_schedule->payment_link_status)
                                                    <a href="{{ route('master.batches.start-recieve-payment', $batch_schedule->id) }}"
                                                        title="Start Payment Link">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-dollar-sign">
                                                            <line x1="12" y1="1" x2="12"
                                                                y2="23">
                                                            </line>
                                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endif
                                                @if ($batch_schedule->is_session_completed == 2)
                                                    <a href="{{ route('master.batches.mark-session-completed', $batch_schedule->id) }}"
                                                        title="Mark Session As Completed">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-check">
                                                            <polyline points="20 6 9 17 4 12"></polyline>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endif
                                                @if ($batch_schedule->is_session_completed == 0)
                                                    <a href="{{ route('master.batches.mark-session-active', $batch_schedule->id) }}"
                                                        title="Mark Session As Active">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-activity">
                                                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                                        </svg>
                                                    </a>
                                                    &nbsp;&nbsp;
                                                @endif
                                                <a href="{{ route('master.batches.view-session-assignment', $batch_schedule->id) }}"
                                                    title="View Session Assignment">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-eye">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </a>
                                                &nbsp;&nbsp;
                                                <a href="{{ route('master.batches.update-rating-start-status', $batch_schedule->id) }}"
                                                    title="{{ $batch_schedule->is_coach_rating_started ? 'Stop Coach Rating' : 'Start Coach Rating' }}">
                                                    @if ($batch_schedule->is_coach_rating_started)
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-zap-off">
                                                            <polyline points="12.41 6.75 13 2 10.57 4.92"></polyline>
                                                            <polyline points="18.57 12.91 21 10 15.66 10"></polyline>
                                                            <polyline points="8 8 3 14 12 14 11 22 16 16"></polyline>
                                                            <line x1="1" y1="1" x2="23"
                                                                y2="23"></line>
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-zap">
                                                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                                        </svg>
                                                    @endif
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
            {{-- End Coaches --}}

            {{-- Coach Registration Banners --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Coach Registration Banners</h4><br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Banner </th>
                                    <th>Batch</th>
                                    <th>Status</th>
                                    <th>Terms and Conditions</th>
                                    <th>Is Mark Agree Button</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->banners as $key => $batch_banner)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ asset('uploads/batch-banners/' . $batch_banner->banner) }}"
                                                target="_blank">
                                                <img src="{{ asset('uploads/batch-banners/' . $batch_banner->banner) }}"
                                                    width="50px" alt="no-image">
                                            </a>
                                        </td>
                                        <td>{{ $batch->name }}</td>

                                        <td>

                                            @if ($batch_banner->is_active)
                                                <a
                                                    href="{{ route('master.batches.update-batch-banner-active-status', $batch_banner->id) }}"><span
                                                        class="badge bg-success">Active</span></a>
                                            @else
                                                <a
                                                    href="{{ route('master.batches.update-batch-banner-active-status', $batch_banner->id) }}"><span
                                                        class="badge bg-danger">Inactive</a>
                                            @endif
                                        </td>
                                        <td>
                                            <textarea class="form-control" disabled>{{ $batch_banner->terms_and_conditions }}</textarea>
                                        </td>
                                        <td>{{ $batch_banner->is_i_agree_mark ? 'Yes' : 'No' }}</td>
                                        <td>{{ $batch_banner->created_at->format('d-m-Y') }}</td>
                                        <td>

                                            @can('batch edit')
                                                <a href="javascript:void(0)" data-bs-toggle="modal"
                                                    data-bs-target="#start-registration-modal" class="edit-batch-banner"
                                                    data-url="{{ route('master.batches.update-batch-banner', $batch_banner->id) }}"
                                                    data-batch-banner="{{ $batch_banner }}"><svg
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-edit-2">
                                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z">
                                                        </path>
                                                    </svg>
                                                </a>
                                                &nbsp;&nbsp;
                                            @endcan
                                            @can('batch delete')
                                                <a href="javascript:void(0)"
                                                    onclick="document.getElementById('delete-batch-banner-{{ $batch->id }}').submit()">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-delete">
                                                        <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                        <line x1="18" y1="9" x2="12" y2="15">
                                                        </line>
                                                        <line x1="12" y1="9" x2="18" y2="15">
                                                        </line>
                                                    </svg>
                                                </a>
                                                <form
                                                    action="{{ route('master.batches.delete-batch-banner', $batch_banner->id) }}"
                                                    method="post" id="delete-batch-banner-{{ $batch->id }}"
                                                    class="d-none">
                                                    @csrf
                                                </form>
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
            {{-- End Registration Banners --}}
        </div>
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card radius-10">
                <div class="card-body">
                    <h5 class="mb-3">Actions</h5>
                    @if ($batch->start_coach_registration)
                        <form action="{{ route('master.batches.stop-coach-registration', $batch->id) }}" method="post">
                            @csrf
                            <button class="btn btn-danger">Stop Coach Registration</button>
                        </form>
                    @else
                        <button class="btn btn-success" data-bs-toggle="modal" id="start-registration-btn"
                            data-url="{{ route('master.batches.start-coach-registration', $batch->id) }}"
                            data-bs-target="#start-registration-modal">Start
                            Coach Registration</button>
                    @endif
                    @if ($batch->status !== 'completed')
                        <a href="javascript:void(0)"
                            onclick="document.getElementById('mark-batch-completed-{{ $batch->id }}').submit()"
                            class="btn btn-primary mt-3">Mark Batch Completed</a>
                        <form action="{{ route('master.batches.mark-batch-completed', $batch->id) }}"
                            id="mark-batch-completed-{{ $batch->id }}" method="post" class="d-none">
                            @csrf
                        </form>
                    @endif
                    @if ($batch->status == 'completed')
                    <a href="javascript:void(0)"
                        onclick="document.getElementById('mark-batch-completed-{{ $batch->id }}').submit()"
                        class="btn btn-primary mt-3">Remove Batch Completed</a>
                    <form action="{{ route('master.batches.mark-batch-completed', $batch->id) }}"
                        id="mark-batch-completed-{{ $batch->id }}" method="post" class="d-none">
                        @csrf
                    </form>
                @endif
                    <button class="btn btn-secondary mt-3" data-bs-toggle="modal" data-bs-target="#add-group-modal"
                        id="add-group-btn" data-url="{{ route('master.batches.add-group', $batch->id) }}">Add
                        Group</button>
                    <button class="btn btn-secondary mt-3" data-bs-toggle="modal"
                        data-bs-target="#add-participant-to-group-modal" id="add-participant-to-group-btn"
                        data-url="{{ route('master.batches.add-participant-to-group') }}">Add
                        Participant To Group</button>
                    {{-- @if ($batch->schedules->count() != $batch->number_of_sessions) --}}
                    <button class="btn btn-secondary mt-3" data-bs-toggle="modal"
                        data-bs-target="#add-batch-schedule-modal" id="add-batch-schedule-btn">Update
                        Batch Schedule</button>
                    {{-- @endif --}}
                </div>
            </div>

            <div class="card radius-10">
                <div class="card-body">
                    <h5 class="mb-3">Batch Details</h5>
                    <h6 class="">Batch Name: {{ $batch->name }}</h6>
                    <h6 class="">Program: {{ $batch->course ? $batch->course->name : '' }}</h4>
                        <h6 class="">Number of Sessions: {{ $batch->number_of_sessions }}</h6>
                        <h6 class="">Fee One Time: {{ $batch->registration_fee }}</h6>
                        <h6 class="">Incidental Charges: {{ $batch->fee_per_session }}</h6>
                        <h6 class="">Start Date: {{ $batch->start_date }}</h6>
                        <h6 class="">End Date: {{ $batch->end_date }}</h6>
                        <h6 class="">Created By: {{ $batch->addedBy ? $batch->addedBy->name : '' }}</h6>
                        <h6 class="">Status: {{ ucfirst($batch->status) }}</h6>
                        <h6 class="">Is Active: {{ $batch->is_active ? 'Yes' : 'No' }}</h6>
                        <h6 class="">Is One session advance fee:
                            {{ $batch->is_one_session_advance_payment ? 'Yes' : 'No' }}</h6>
                </div>
            </div>

            <div class="card radius-10">
                <div class="card-body">
                    <h5 class="card-title">Batch Image</h5>
                    <img src="{{ asset('uploads/batches/' . $batch->image) }}" class="card-img-top" alt="...">
                    <br> <br>
                    <h5 class="card-title">Batch Description</h5>
                    <p class="card-text">{{ $batch->description }}</p>

                </div>
            </div>



        </div>
    </div>

    {{-- Start Coach Registration Modal --}}
    <form action="{{ route('master.batches.start-coach-registration', $batch->id) }}" method="post"
        enctype="multipart/form-data" id="start-registration-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="start-registration-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Batch Name - {{ $batch->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="banner" class="form-label">Upload Banner for App </label>
                            <input type="file" class="form-control" id="banner" name="banner"
                                placeholder="Upload Banner For App" required>

                        </div>
                        <div class="form-group m-2">
                            <label for="terms_and_conditions" class="form-label">Terms and Conditions</label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" class="form-control" required></textarea>
                        </div>
                        <div class="form-check form-switch m-2">
                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked"
                                name="is_i_agree_mark" value="1">
                            <label class="form-check-label" for="flexSwitchCheckChecked">Enable I agree mark</label>
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

    {{-- Add Group Modal --}}
    <form action="{{ route('master.batches.add-group', $batch->id) }}" method="post" enctype="multipart/form-data"
        id="add-group-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="add-group-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Batch Name - {{ $batch->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="name" class="form-label">Group Name </label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter Group Name" required>
                        </div>
                        <div class="form-group m-2">
                            <label for="coach_id" class="form-label">Coach </label>
                            <select name="coach_id" id="coach_id" class="form-control" required>
                                <option label="Select"></option>
                                @foreach ($batch->coaches as $key => $participant)
                                    <option value="{{ $participant->id }}">{{ $participant->first_name }}
                                        {{ $participant->last_name }} ({{ $participant->mobile }})</option>
                                @endforeach
                            </select>
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

    {{-- Add Participant To Group Modal --}}
    <form action="{{ route('master.batches.add-participant-to-group') }}" method="post" enctype="multipart/form-data"
        id="add-participant-to-group-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="add-participant-to-group-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Batch Name - {{ $batch->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="batch_group_id" class="form-label">Select Group </label>
                            <select name="batch_group_id" id="batch_group_id" class="form-control" required>
                                <option label="Select"></option>
                                @foreach ($batch->groups as $batch_group)
                                    <option value="{{ $batch_group->id }}">{{ $batch_group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group m-2">
                            <label for="participant_id" class="form-label">Select Participant </label>
                            <select name="participant_id" id="participant_id" class="form-control" required>
                                <option label="Select"></option>
                                @foreach ($participantsNotInAnyGroup as $key => $participant)
                                    <option value="{{ $participant->id }}">{{ $participant->first_name }}
                                        {{ $participant->last_name }} ({{ $participant->mobile }})</option>
                                @endforeach
                            </select>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End  --}}

    {{-- View Group Participant Modal --}}
    <div class="modal fade" tabindex="-1" id="view-group-participants-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="group_participants_title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="group_participants_tbody">

                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
    {{-- End --}}

    {{-- Add Batch Schedule Modal --}}
    {{-- @if ($batch->number_of_sessions > 0 && $batch->schedules->count() != $batch->number_of_sessions) --}}
    <form action="{{ route('master.batches.add-batch-schedule') }}" method="post">
        @csrf
        <div class="modal fade" tabindex="-1" id="add-batch-schedule-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="group_participants_title">Add Batch Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @if ($batch->schedules->count() > 0)
                                @foreach ($batch->schedules as $batch_schedule)
                                    <div class="col-4">

                                        <div class="form-group m-2">
                                            <label for="name{{ $loop->iteration }}" class="form-label">Session
                                                {{ $loop->iteration }} Name
                                            </label>
                                            <input type="text" class="form-control" id="name{{ $loop->iteration }}"
                                                name="name[]" value="{{ $batch_schedule->name }}"
                                                placeholder="Enter Session Name" required>
                                        </div>
                                    </div>
                                    <div class="col-4">

                                        <div class="form-group m-2">
                                            <label for="date{{ $loop->iteration }}" class="form-label">Session
                                                {{ $loop->iteration }} Date </label>
                                            <input type="date" class="form-control" id="date{{ $loop->iteration }}"
                                                name="date[]" value="{{ $batch_schedule->date }}"
                                                placeholder="Select Session Date" required>
                                        </div>
                                    </div>
                                    <div class="col-4">

                                        <div class="form-group m-2">
                                            <label for="amount{{ $loop->iteration }}" class="form-label">Session
                                                {{ $loop->iteration }} Amount </label>
                                            <input type="number" class="form-control"
                                                id="amount{{ $loop->iteration }}" name="amount[]"
                                                value="{{ $batch_schedule->amount }}"
                                                placeholder="Select Session amount" required
                                                {{ $batch_schedule->payment_link_status ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                @for ($i = 1; $i <= $batch->number_of_sessions; $i++)
                                    <div class="col-4">

                                        <div class="form-group m-2">
                                            <label for="name{{ $i }}" class="form-label">Session
                                                {{ $i }} Name
                                            </label>
                                            <input type="text" class="form-control" id="name{{ $i }}"
                                                name="name[]" placeholder="Enter Session Name" required>
                                        </div>
                                    </div>
                                    <div class="col-4">

                                        <div class="form-group m-2">
                                            <label for="date{{ $i }}" class="form-label">Session
                                                {{ $i }} Date </label>
                                            <input type="date" class="form-control" id="date{{ $i }}"
                                                name="date[]" placeholder="Select Session Date" required>
                                        </div>
                                    </div>
                                    <div class="col-4">

                                        <div class="form-group m-2">
                                            <label for="amount{{ $i }}" class="form-label">Session
                                                {{ $i }} Amount </label>
                                            <input type="number" class="form-control" id="amount{{ $i }}"
                                                name="amount[]" placeholder="Select Session amount" required>
                                        </div>
                                    </div>
                                @endfor
                            @endif
                        </div>


                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
    {{-- @endif --}}
    {{-- End --}}

    {{-- Update Batch Schedule Modal --}}
    <form action="" method="post" id="update-batch-schedule-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="edit-schedule-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Batch Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="name" class="form-label">Session
                                Name
                            </label>
                            <input type="text" class="form-control" id="update-name" name="name"
                                placeholder="Enter Session Name" required>

                        </div>
                        <div class="form-group m-2">

                            <label for="date" class="form-label">Session
                                Date </label>
                            <input type="date" class="form-control" id="update-date" name="date"
                                placeholder="Select Session Date" required>

                        </div>
                        <div class="form-group m-2">

                            <label for="amount" class="form-label">Session
                                Amount </label>
                            <input type="number" class="form-control" id="update-amount" name="amount"
                                placeholder="Select Session amount" required>

                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End  --}}
@endsection
@section('custom_js')
    <script>
        $(document).on('click', '#start-registration-btn', function() {
            $('#start-registration-form').prop('action', $(this).data('url'));
            $('#banner').prop('required', true);
        });
        $(document).on('click', '.edit-batch-banner', function() {
            $('#start-registration-form').prop('action', $(this).data('url'));
            var batch_banner = $(this).data('batch-banner');
            $('#terms_and_conditions').val(batch_banner.terms_and_conditions);
            $('#banner').prop('required', false);
            if (batch_banner.is_i_agree_mark == 1) {
                $('#flexSwitchCheckChecked').prop('checked', true);
            } else {
                $('#flexSwitchCheckChecked').prop('checked', false);
            }
        });
        $(document).on('click', '#add-group-btn', function() {
            $('#add-group-form').prop('action', $(this).data('url'));
        });
        $(document).on('click', '.edit-batch-group-btn', function() {
            $('#add-group-form').prop('action', $(this).data('url'));
            var batch_group = $(this).data('batch-group');
            $('#name').val(batch_group.name);
            $('#coach_id').val(batch_group.coach_id).trigger('change');
        })
        $(document).ready(function() {
            $('#coach_id').select2({
                placeholder: 'Select Coach',
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true,
                dropdownParent: $('#add-group-modal')
            })
        });
        $(document).on('click', '.view-group-participants-btn', function() {
            var batch_group_participants = $(this).data('batch-group-participants');
            var batch_group = $(this).data('batch-group');
            $('#group_participants_title').text('Group Name - ' + batch_group.name);
            $('#group_participants_tbody').empty();
            $.each(batch_group_participants, function(key, value) {
                var url = "{{ route('master.batches.remove-participant-from-group', ':id') }}".replace(
                    ':id', value.id);
                $('#group_participants_tbody').append(
                    '<tr>' +
                    '<td>' + (key + 1) + '</td>' +
                    '<td>' + value.participant.first_name + ' ' + value.participant.last_name +
                    '</td>' +
                    '<td>' +
                    '<a href="' + url +
                    '" class="btn btn-success btn-sm edit-batch-participant-btn">Remove</a>' +
                    '</td>' +
                    '</tr>'
                )
            });
        });
        $(document).on('click', '.edit-batch-schedule-btn', function() {
            var batch_schedule = $(this).data('batch-schedule');
            $('#update-name').val(batch_schedule.name);
            $('#update-date').val(batch_schedule.date);
            $('#update-amount').val(batch_schedule.amount);
            if (batch_schedule.payment_link_status == 1) {
                $('#update-amount').prop('readonly', true);
            } else {
                $('#update-amount').prop('readonly', false);
            }
            $('#update-batch-schedule-form').prop('action', $(this).data('url'));
        })
    </script>
@endsection
