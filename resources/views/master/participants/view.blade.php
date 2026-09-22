@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Batch</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Participant Details -
                        {{ $participant->first_name }} {{ $participant->last_name }}</li>
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
                    <a class="dropdown-item" href="{{ route('master.participants.edit', $participant->id) }}">Edit</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-9">
            {{-- Enrolled Batches List --}}
            <div class="card">
                <div class="card-body">
                    @can('batch add')
                        <a href="javascript:void(0);">
                            <div class="col"style="float: right">
                                <button type="button" class="btn btn-primary px-5" data-bs-toggle="modal"
                                    data-bs-target="#add-to-new-batch-modal">Add To New Batch</button>
                            </div>
                        </a>
                    @endcan
                    <h4 class="mb-2">Enrolled Batches of {{ $participant->first_name }} {{ $participant->last_name }}</h4>
                    <br>
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
                                    <th>Is Registration Fees Paid</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($participant->participantBatches as $key => $batch)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('master.batches.view', $batch->id) }}">{{ $batch->name }}</a>
                                        </td>
                                        <td>{{ $batch->course ? $batch->course->name : '' }}</td>
                                        <td>{{ $batch->number_of_sessions }}</td>
                                        <td>{{ $batch->registration_fee }}</td>
                                        <td>{{ $batch->fee_per_session }}</td>
                                        <td>

                                            @if ($batch->pivot->is_registration_fees_paid)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-danger"> No</span>
                                            @endif
                                        </td>
                                        <td>

                                            @can('participant edit')
                                                @if (!$batch->pivot->is_registration_fees_paid)
                                                    <a href="{{ route('master.participants.add-registration-payment', $batch->pivot->id) }}"
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
                                                    &nbsp;&nbsp;
                                                @endif
                                            @endcan
                                            @can('participant view')
                                                <a href="{{ route('master.participants.view-sessions', ['participant_id' => $participant->id, 'batch_id' => $batch->id]) }}"
                                                    title="View Sessions">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-eye">
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
            {{-- End Enrolled Batches List --}}

            {{-- Payments List --}}
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-2">Payments List of {{ $participant->first_name }} {{ $participant->last_name }}</h4>
                    <br>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered dataTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Batch </th>
                                    <th>Payment Image </th>
                                    <th>Transaction Id</th>
                                    <th>Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Payment For</th>
                                    <th>Session Number</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($participant->payments as $key => $participant_payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if ($participant_payment->batch)
                                                <a
                                                    href="{{ route('master.batches.view', $participant_payment->batch->id) }}">{{ $participant_payment->batch->name }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($participant_payment->payment_image)
                                                <a href="{{ asset('uploads/participant-payment/' . $participant_payment->payment_image) }}"
                                                    target="_blank"> <img
                                                        src="{{ asset('uploads/participant-payment/' . $participant_payment->payment_image) }}"
                                                        alt="" width="50px"></a>
                                            @endif
                                        </td>
                                        <td>{{ $participant_payment->transaction_id }}</td>
                                        <td>{{ $participant_payment->amount }}</td>
                                        <td>{{ $participant_payment->payment_mode }}</td>
                                        <td>{{ $participant_payment->payment_for }}</td>
                                        <td>{{ $participant_payment->session_number }}</td>
                                        <td>{{ $participant_payment->created_at->format('d-m-Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{-- End Payments List --}}

        </div>
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card radius-10">
                <div class="card-body">
                    <h5 class="mb-3">Participant Details</h5>
                    <h6 class="">First Name: {{ $participant->first_name }}</h6>
                    <h6 class="">Last Name: {{ $participant->last_name }}</h6>
                    <h6 class="">Mobile Number: {{ $participant->mobile }}</h6>
                    <h6 class="">Email: {{ $participant->email }}</h6>
                    <h6 class="">Birth Date: {{ $participant->birth_date ? $participant->birth_date->format('d-m-Y') : 'N/A' }}</h6>
                    <h6 class="">Address: {{ $participant->address }}</h6>
                    <h6 class="">City: {{ $participant->city }}</h6>
                    <h6 class="">State: {{ $participant->state }}</h6>
                    <h6 class="">Country: {{ $participant->country }}</h6>
                    <h6 class="">Reference By: <span class="badge bg-light-primary text-primary">{{ $participant->reference ?: 'N/A' }}</span></h6>
                    @if ($participant->reference_detail)
                        <h6 class="">Reference Detail: {{ $participant->reference_detail }}</h6>
                    @endif
                    @if ($participant->profile_photo)
                        <h6 class="">Profile Photo: <a
                                href="{{ asset('uploads/participants/profile-photos/' . $participant->profile_photo) }}"
                                target="_blank"><img
                                    src="{{ asset('uploads/participants/profile-photos/' . $participant->profile_photo) }}"
                                    width="100"></a></h6>
                    @endif
                </div>
            </div>



        </div>
    </div>
    {{-- Add To New Batch Modal --}}
    <form action="{{ route('master.participants.add-to-another-batch', $participant->id) }}" method="post">
        @csrf
        <div class="modal fade" tabindex="-1" id="add-to-new-batch-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add To New Batch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="batch_id" class="form-label">Select Batch </label>
                            <select name="batch_id" id="batch_id" class="form-control" required>
                                <option label="">Select</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                @endforeach
                            </select>

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
    {{-- End Add To New Batch Modal --}}
@endsection
