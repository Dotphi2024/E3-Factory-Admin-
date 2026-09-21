@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Receive Payment</div>
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
    </div>
    {{-- Enrolled Batches List --}}
    <div class="card">
        <div class="card-body">

            <h4 class="mb-2">Enrolled Batches of {{ $participant->first_name }} {{ $participant->last_name }}</h4>
            <br>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name </th>
                            <th>Program</th>
                            <th>Expected Payment</th>
                            <th>Paid Payment</th>
                            <th>Due Payment</th>
                            <th>Registration Due Payment</th>
                            <th>Session Due Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($participant->participantBatches as $key => $batch)
                            @php
                                $expected_payment = $batch->registration_fee;
                                $paid_payment = $batch->payments
                                    ->where('participant_id', $participant->id)
                                    ->where('payment_for', 'registration')
                                    ->sum('amount');
                                $registration_fees_paid = $paid_payment;
                                $total_active_session_fees_paid = 0;

                                $payment_active_sessions = $batch->schedules->where('payment_link_status', 1);

                                foreach ($payment_active_sessions as $payment_active_session) {
                                    $expected_payment += $payment_active_session->amount;
                                    $paid_session_fees = $batch->payments
                                        ->where('participant_id', $participant->id)
                                        ->where('payment_for', 'session_fee')
                                        ->where('session_number', $payment_active_session->session_number)
                                        ->sum('amount');
                                    $paid_payment += $paid_session_fees;
                                    $total_active_session_fees_paid += $paid_session_fees;
                                }

                                $due_payment = $expected_payment - $paid_payment;
                                $due_registration_payment = $batch->registration_fee - $registration_fees_paid;
                                $session_due_payment =
                                    $payment_active_sessions->sum('amount') - $total_active_session_fees_paid;

                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('master.batches.view', $batch->id) }}">{{ $batch->name }}</a>
                                </td>
                                <td>{{ $batch->course ? $batch->course->name : '' }}</td>

                                <td>{{ $expected_payment }}</td>
                                <td>{{ $paid_payment }}</td>
                                <td>{{ $due_payment }}</td>
                                <td>{{ $due_registration_payment }}</td>
                                <td>{{ $session_due_payment }}</td>

                                <td>

                                    @can('participant edit')
                                        @if (!$batch->pivot->is_registration_fees_paid)
                                            <button href="javascript:void(0);" data-bs-toggle="modal"
                                                data-bs-target="#receiveRegistrationDuePaymentModal"
                                                class="btn btn-primary receive-registration-due-payment-btn"
                                                data-participant-id="{{ $participant->id }}"
                                                data-registration-fees-paid="{{ $registration_fees_paid }}"
                                                data-batch-id="{{ $batch->id }}"
                                                data-registration-fees-due="{{ $due_registration_payment }}"
                                                title="Add Registration Due Payment">
                                                Add Registration Payment
                                            </button>
                                            &nbsp;&nbsp;
                                        @endif
                                        @if ($session_due_payment > 0)
                                            <a href="{{ route('master.participants.add-session-due-payment', ['participant_id' => $participant->id, 'batch_id' => $batch->id]) }}"
                                                class="btn btn-success">Add Session Due Payment</a>
                                        @endif
                                        @if ($due_payment > 0)
                                            &nbsp;&nbsp;
                                            <button type="button" data-bs-toggle="modal"
                                                data-bs-target="#sendWhatsAppModal"
                                                class="btn btn-success send-whatsapp-btn"
                                                data-participant-id="{{ $participant->id }}"
                                                data-participant-mobile="{{ $participant->mobile }}"
                                                data-participant-name="{{ $participant->first_name }} {{ $participant->last_name }}"
                                                data-batch-id="{{ $batch->id }}"
                                                data-batch-name="{{ $batch->name }}"
                                                data-pending-amount="{{ $due_payment }}"
                                                title="Send WhatsApp for Pending Amount">
                                                <i class="bi bi-whatsapp"></i> Send WhatsApp
                                            </button>
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
                            <th>Invoice</th>
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
                                <td>
                                    <a href="{{ route('master.participants.download-invoice', $participant_payment->id) }}"
                                        target="_blank" class="btn btn-sm btn-primary">Invoice</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- End Payments List --}}

    {{-- Receive Due Payment Modal --}}
    @include('master.participants.receive-registration-due-payment-modal')
    {{-- End Receive Due Payment Modal --}}

    {{-- Send WhatsApp Modal --}}
    @include('master.participants.send-whatsapp-modal')
    {{-- End Send WhatsApp Modal --}}
@endsection



@section('custom_js')
    <script>
        $(document).on('click', '.receive-registration-due-payment-btn', function() {
            $('#participant_id').val($(this).data('participant-id'));
            $('#batch_id').val($(this).data('batch-id'));
            $('#due_amount').val($(this).data('registration-fees-due'));
            $('#registration_fees_due').val($(this).data('registration-fees-due'));
            $('#registration_fees_paid').val($(this).data('registration-fees-paid'));
        })

        $(document).on('click', '.send-whatsapp-btn', function() {
            var participantId = $(this).data('participant-id');
            var participantMobile = $(this).data('participant-mobile');
            var participantName = $(this).data('participant-name');
            var batchId = $(this).data('batch-id');
            var batchName = $(this).data('batch-name');
            var pendingAmount = $(this).data('pending-amount');

            $('#wa_participant_id').val(participantId);
            $('#wa_batch_id').val(batchId);
            $('#wa_mobile').val(participantMobile);
            $('#wa_pending_amount').val(pendingAmount);

            var message = "Dear " + participantName + ",\n\nThis is a friendly reminder that you have a pending amount of Rs. " + pendingAmount + " for the batch '" + batchName + "'.\n\nPlease process the payment at your earliest convenience.\n\nBest regards,\nE3 Admin Team";

            $('#wa_message').val(message);
        });
    </script>
@endsection
