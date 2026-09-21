@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Add Participant Payment -
            {{ $participant->first_name . ' ' . $participant->last_name }}</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Payment</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation"
        action="{{ route('master.participants.store-registration-payment', $participant->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Payment Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="transaction_id" class="form-label">Transaction ID </label>
                                    <input type="text" name="transaction_id" id="transaction_id" class="form-control"
                                        value="{{ old('transaction_id') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="amount" class="form-label">Amount
                                        {{ $participant->batch && $participant->batch->is_one_session_advance_payment ? 'Registration + One Session' : 'Registration' }}
                                    </label>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                        value="{{ $participant->batch && $participant->batch->is_one_session_advance_payment ? $participant->batch->registration_fee + $participant->batch->fee_per_session : $participant->batch->registration_fee }}"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_mode" class="form-label">Payment Mode </label>
                                    <select name="payment_mode" id="payment_mode" class="single-select"
                                        data-placeholder="Select" required>
                                        <option label="Select"></option>
                                        <option value="Gpay">Gpay</option>
                                        <option value="Phonepe">Phonepe</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="payment_image" class="form-label">Payment Image </label>
                                    <input type="file" class="form-control" id="payment_image" name="payment_image"
                                        required>
                                </div>


                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>
    <!--end row-->
@endsection
