@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Add Participant</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Participant</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.participants.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Participant Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="batch_id" class="form-label">Select Batch </label>
                                    <select name="batch_id" id="batch_id" class="single-select" data-placeholder="Select"
                                        required>
                                        <option label="Select"></option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name </label>
                                    <input type="text" class="form-control" id="first_name"
                                        value="{{ old('first_name') }}" name="first_name" placeholder="Enter First Name"
                                        required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name </label>
                                    <input type="text" class="form-control" id="last_name" value="{{ old('last_name') }}"
                                        name="last_name" placeholder="Enter Last Name" required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label">Mobile Number </label>
                                    <input type="text" class="form-control" id="mobile" value="{{ old('mobile') }}"
                                        name="mobile" placeholder="Enter Last Name" required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ old('email') }}" placeholder="Email">
                                </div>
                                <div class="col-md-6">
                                    <label for="birth_date" class="form-label">Birth Date</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date"
                                        value="{{ old('birth_date') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea name="address" id="address" class="form-control">{{ old('address') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <select name="country" id="country" class="single-select">
                                        <option label="Select"></option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country }}">{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="state" class="form-label">State</label>
                                    <select name="state" id="state" class="form-control">
                                        <option label="Select"></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">City</label>
                                    <select name="city" id="city" class="form-control">
                                        <option label="Select"></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="reference" class="form-label">Reference By</label>
                                    <select name="reference" id="reference" class="form-control" required>
                                        <option label="Select"></option>
                                        <option value="Member">Member</option>
                                        <option value="Social Media">Social Media</option>
                                        <option value="Advertisement">Advertisement</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="reference_detail_div">
                                    <label for="reference_detail" class="form-label">Reference Detail</label>
                                    <select name="reference_detail" id="reference_detail_social" class="form-control"
                                        style="display: none">

                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="Twitter">Twitter</option>
                                        <option value="LinkedIn">LinkedIn</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <select name="reference_detail" id="reference_detail_advertisement"
                                        class="form-control" style="display: none">

                                        <option value="Google">Google</option>
                                        <option value="TV">TV</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <select name="reference_detail" id="reference_detail_member"
                                        style="display: none">

                                        @foreach ($participants as $participant)
                                            <option value="{{ $participant->id }}">{{ $participant->first_name }}
                                                {{ $participant->last_name }}({{ $participant->mobile }})</option>
                                        @endforeach
                                    </select>
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
        $(document).on('change', '#reference', function() {
            let reference = $(this).val();
            if(reference == 'Social Media'){
                $('#reference_detail_social').show();
                $('#reference_detail_advertisement').hide();
                $('#reference_detail_member').select2('destroy').hide();
            }else if(reference == 'Advertisement'){
                $('#reference_detail_advertisement').show();
                $('#reference_detail_social').hide();
                $('#reference_detail_member').select2('destroy').hide();
            } else if(reference == 'Member'){
                $('#reference_detail_member').select2({
                    tags: true,
                    theme: "bootstrap4",
                    width: '100%',
                    placeholder: "Select",
                    allowClear: true
                });
                $('#reference_detail_member').show();
                $('#reference_detail_advertisement').hide();
                $('#reference_detail_social').hide();
            }
        })
        $(document).on('change', '#country', function() {
            let country = $(this).val();
            if (country) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('get-states') }}",
                    data: {
                        "country": country,
                        "_token": "{{ csrf_token() }}",
                    },
                    dataType: "json",
                    success: function(response) {
                        response.sort((a, b) => (a.name > b.name) ? 1 : -1);

                        $('#state').empty();
                        $('#state').append('<option label="Select"></option>');
                        $.each(response, function(key, value) {
                            $('#state').append('<option value="' + value.name + '">' + value
                                .name + '</option>');
                        });
                        $('#city').empty();
                        $('#city').append('<option label="Select"></option>');
                        $('#state').select2({
                            placeholder: "Select",
                            theme: "bootstrap4",
                            width: '100%'
                        });
                    }
                });
            } else {
                $('#state').empty();
                $('#state').append('<option label="Select"></option>');
                $('#city').empty();
                $('#city').append('<option label="Select"></option>');
            }
        });
        $(document).on('change', '#state', function() {
            let state = $(this).val();
            let country = $('#country').val();

            if (state && country) {
                $.ajax({
                    url: '{{ route('get-cities') }}',
                    type: 'POST',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        state: state,
                        country: country
                    },
                    dataType: 'json',
                    success: function(data) {
                        data.sort((a, b) => (a.name > b.name) ? 1 : -1);

                        $('#city').empty();
                        $('#city').append('<option label="Select"></option>');

                        $.each(data, function(key, value) {
                            $('#city').append('<option value="' + value.name + '">' + value
                                .name +
                                '</option>');
                        });
                        $('#city').select2({
                            placeholder: "Select",
                            theme: "bootstrap4",
                            width: '100%'
                        });
                    },
                });
            } else {
                // Handle the case when no state is selected
                $('#city').empty();
                $('#city').append('<option label="Select"></option>');
            }
        });
    </script>
@endsection
