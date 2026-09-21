@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Receive Session Due Payments</div>
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
    <div class="card">
        <div class="card-body">
            <form action="{{ route('master.participants.store-session-due-payment') }}" method="post"
                enctype="multipart/form-data">
                @csrf
                <div class="row g-3 needs-validation">
                    <div class="col-md-6">
                        <label for="session_number" class="form-label">Select Sessions
                            <span class="text-danger">*</span>
                        </label>
                        @foreach ($batch->schedules->where('payment_link_status', 1)->whereNotIn('session_number', $participant->payments ? $participant->payments->pluck('session_number') : [])->sortBy('session_number')->values() as $key => $batch_schedule)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="session_number[]"
                                    value="{{ $batch_schedule->session_number }}"
                                    data-session-amount="{{ $batch_schedule->amount }}"
                                    id="session-checkbox-{{ $key }}" {{ $key == 0 ? '' : 'disabled' }}
                                    {{ $key == 0 ? 'required' : '' }} />
                                <label class="form-check-label"
                                    for="session-checkbox-{{ $key }}">{{ $batch_schedule->session_number }}</label>
                            </div>
                        @endforeach


                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Amount
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control" name="amount" id="amount" required readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="transaction_id" class="form-label">Transaction ID </label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                            value="{{ old('transaction_id') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="payment_mode" class="form-label">Payment Mode </label>
                        <select name="payment_mode" id="payment_mode" class="form-select" data-placeholder="Select"
                            required>
                            <option label="Select"></option>
                            <option value="Gpay">Gpay</option>
                            <option value="Phonepe">Phonepe</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="payment_image" class="form-label">Payment Image </label>
                        <input type="file" class="form-control" id="payment_image" name="payment_image">
                    </div>
                    <div class="col-12">
                        <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                        <input type="hidden" name="batch_id" value="{{ $batch->id }}">
                        <button class="btn btn-primary" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('custom_js')
    <script>
        $(document).ready(function() {
            let totalAmount = 0;
            let checkboxCount = $("input[type='checkbox']").length; // Total number of checkboxes

            // Handle checkbox click event
            $("input[type='checkbox']").on('change', function() {
                // Reset total amount and calculate correctly based on checked checkboxes
                totalAmount = 0;

                // Loop through each checkbox and calculate the amount dynamically
                $("input[type='checkbox']").each(function() {
                    if ($(this).is(':checked')) {
                        // Add amount when checkbox is checked
                        totalAmount += parseFloat($(this).data("session-amount"));
                    }
                });

                // Update the amount input field with the total amount
                $("#amount").val(totalAmount);

                // Enable/Disable checkboxes based on selected checkboxes
                $("input[type='checkbox']").each(function(index) {
                    // If previous checkbox is checked, enable the next checkbox
                    if ($(this).is(':checked')) {
                        if (index + 1 < checkboxCount) {
                            $("#session-checkbox-" + (index + 1)).prop('disabled',
                                false); // Enable the next checkbox
                        }
                    } else {
                        // If checkbox is unchecked, disable all following checkboxes and uncheck them
                        if (index + 1 < checkboxCount) {
                            $("#session-checkbox-" + (index + 1)).prop('disabled', true).prop(
                                'checked', false);
                        }
                    }
                });
            });

            // Enable the first checkbox when the page loads
            $("#session-checkbox-0").prop('disabled', false);
        });
    </script>
@endsection
