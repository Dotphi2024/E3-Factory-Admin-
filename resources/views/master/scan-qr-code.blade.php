@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Participant Details</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Participant Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card radius-10">
                <div class="card-body">

                    <h5 class="mb-3">Details</h5>
                    <div class="mb-4 d-flex flex-column gap-3 align-items-center justify-content-center">
                        <div class="user-change-photo shadow">
                            <img src="{{ asset('uploads/participants/profile-photos/' . $participant->profile_photo) }}"
                                alt="{{ $participant->first_name . ' ' . $participant->last_name }}">
                        </div>

                    </div>
                    <h5 class="mb-0 mt-4">User Information</h5>
                    <hr>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">First Name</label>:
                            <b>{{ $participant->first_name }}</b>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Last Name</label>:
                            <b>{{ $participant->last_name }}</b>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Batch</label>:
                            <b>{{ $participant_payment->batch ? $participant_payment->batch->name : '' }}</b>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Session Name</label>:
                            <b>{{ $batch_schedule ? $batch_schedule->name : '' }}</b>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Session Date</label>:
                            <b>{{ $batch_schedule ? $batch_schedule->date : '' }}</b>
                        </div>
                    </div>


                    <div class="text-start mt-3 text-danger">
                        @if ($batch_schedule)
                            @if ($participant_payment->is_qr_used)
                                <b>QR Code Already Used OR Scanned</b>
                            @elseif ($batch_schedule->is_session_completed == 1)
                                <b>Session Completed</b>
                            @elseif ($batch_schedule->is_session_completed == 0)
                                <b>Session Not Started</b>
                            @elseif ($batch_schedule->date < date('Y-m-d') && $batch_schedule->date != date('Y-m-d'))
                                <b>Session Expired</b>
                            @elseif ($batch_schedule->date > date('Y-m-d'))
                                <b>Session Not Yet Started</b>
                            @else
                                <a href="{{ route('entry-user.enter-participant', ['payment_id' => $participant_payment->id, 'participant_id' => $participant->id]) }}"
                                    class="btn btn-primary px-4">Entry</a>
                                <a href="{{ route('entry-user.decline-participant') }}"
                                    class="btn btn-danger px-4">Decline</a>
                            @endif
                        @else
                            <b>No Session Found</b>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
