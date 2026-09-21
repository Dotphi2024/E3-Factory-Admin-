@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Edit Batch</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Batch</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.batches.update', $batch->id) }}" method="POST" autocomplete="off"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Batch Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name </label>
                                    <input type="text" class="form-control" id="name" value="{{ $batch->name }}"
                                        name="name" placeholder="Enter Name" required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="course_id" class="form-label">Course </label>
                                    <select name="course_id" id="course_id" class="form-select" required>
                                        <option label="Select"></option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->id }}" {{ $course->id == $batch->course_id ? 'selected' : '' }}>{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="number_of_sessions" class="form-label">Number of Session</label>
                                    <input type="number" class="form-control" id="number_of_sessions"
                                        name="number_of_sessions" value="{{ $batch->number_of_sessions }}"
                                        placeholder="Number of Sessions" required disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="registration_fee" class="form-label">Fee one time</label>
                                    <input type="number" class="form-control" id="registration_fee"
                                        name="registration_fee" value="{{ $batch->registration_fee }}"
                                        placeholder="Fee one time" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="fee_per_session" class="form-label">Incidental Charge</label>
                                    <input type="number" class="form-control" id="fee_per_session"
                                        name="fee_per_session" value="{{ $batch->fee_per_session }}"
                                        placeholder="Incidental Charge" required>
                                </div>
                               <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input class="result form-control" type="text" id="start_date" name="start_date"
                                        placeholder="Date Picker..." value="{{ date('d-m-Y', strtotime($batch->start_date)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input class="result form-control" type="text" id="end_date" name="end_date"
                                        placeholder="Date Picker..." value="{{ date('d-m-Y', strtotime($batch->end_date)) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="is_one_session_advance_payment" class="form-label">Payment one session advance </label>
                                    <select name="is_one_session_advance_payment" id="is_one_session_advance_payment" class="form-select" required>
                                        <option label="Select"></option>
                                        <option value="1" {{ $batch->is_one_session_advance_payment ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ $batch->is_one_session_advance_payment ? '' : 'selected' }}>No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" name="image" id="image" class="form-control">
                                    @if ($batch->image)
                                        <img src="{{ asset('uploads/batches/' . $batch->image) }}" alt="no-image" width="100px" class="mt-2">
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Description
                                    </label>
                                    <textarea name="description" id="description" class="form-control">{{ $batch->description }}</textarea>
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
@section('custom_js')
    <script>
        $(document).ready(function() {
            $('#start_date').bootstrapMaterialDatePicker({
                format: 'DD-MM-YYYY',
                time: false,
                clearButton: true,
                minDate: new Date()
            }).on('change', function(e, date) {
                $('#end_date').bootstrapMaterialDatePicker('setMinDate', date);
            });
            $('#end_date').bootstrapMaterialDatePicker({
                format: 'DD-MM-YYYY',
                time: false,
                clearButton: true,
            });
        })
    </script>
@endsection
