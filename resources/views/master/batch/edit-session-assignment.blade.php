@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Edit Session Assignment</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Session Assignment</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->
    <form class="row g-3 needs-validation" action="{{ route('master.batches.update-session-assignment', $assignment->id) }}"
        method="POST" autocomplete="off" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Assignment Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="question" class="form-label">Question </label>
                                    <input type="text" class="form-control" id="question"
                                        value="{{ $assignment->question }}" name="question" placeholder="Enter question"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label for="option_type" class="form-label">Option Type</label>
                                    <select name="option_type" id="option_type" class="form-select" required>
                                        <option label="Select"></option>
                                        <option value="manual" {{ $assignment->option_type == 'manual' ? 'selected' : '' }}>
                                            Manual field</option>
                                        <option value="options"
                                            {{ $assignment->option_type == 'options' ? 'selected' : '' }}>Options</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" name="is_file_upload" value="1" {{ $assignment->is_file_upload ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flexSwitchCheckDefault">Enable File Upload</label>
                                    </div>
                                </div>
                                <hr>
                                <div class="options-div"
                                    style="{{ $assignment->option_type == 'options' ? '' : 'display:none;' }}">
                                    <div id="options-container" class="d-flex flex-wrap w-100">
                                        @foreach ($assignment->options as $index => $option)
                                            <div class="option-group d-flex w-100">
                                                <div class="col-md-4 m-2">
                                                    <label for="option" class="form-label">Option</label>
                                                    <input type="text" class="form-control" name="option[]"
                                                        value="{{ $option->option }}" placeholder="Enter option" required>
                                                </div>
                                                <div class="col-md-4 m-2">
                                                    <label for="mark" class="form-label">Mark</label>
                                                    <input type="text" class="form-control" name="mark[]"
                                                        value="{{ $option->mark }}" placeholder="Enter mark" required>
                                                </div>
                                                <div class="col-md-4 m-2 d-flex align-items-center">
                                                    @if ($index == 0 && $assignment->option_type == 'options')
                                                        <!-- Show Add button for the first option if option_type is 'options' -->
                                                        <button type="button"
                                                            class="btn btn-primary mt-4 add-option">+</button>
                                                    @else
                                                        <button type="button"
                                                            class="btn btn-danger mt-4 remove-option">x</button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
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
            // Show or hide the options-div based on the initial option_type value
            $('#option_type').trigger('change');

            // Handle the change event on the option_type select
            $(document).on('change', '#option_type', function() {
                if ($(this).val() == 'options') {
                    $('.options-div').show();
                    $('.options-div input').prop('required', true);
                } else {
                    $('.options-div').hide();
                    $('.options-div input').prop('required', false);
                }
            });

            // Handle adding a new option/mark group
            $(document).on('click', '.add-option', function(e) {
                e.preventDefault();
                let optionGroup = `
            <div class="option-group d-flex w-100">
                <div class="col-md-4 m-2">
                    <label for="option" class="form-label">Option</label>
                    <input type="text" class="form-control" name="option[]" placeholder="Enter option" required>
                </div>
                <div class="col-md-4 m-2">
                    <label for="mark" class="form-label">Mark</label>
                    <input type="text" class="form-control" name="mark[]" placeholder="Enter mark" required>
                </div>
                <div class="col-md-4 m-2 d-flex align-items-center">
                    <button type="button" class="btn btn-danger mt-4 remove-option">x</button>
                </div>
            </div>
        `;
                $('#options-container').append(optionGroup);
            });

            // Handle removing an option/mark group
            $(document).on('click', '.remove-option', function(e) {
                e.preventDefault();
                $(this).closest('.option-group').remove();
            });
        });
    </script>
@endsection
