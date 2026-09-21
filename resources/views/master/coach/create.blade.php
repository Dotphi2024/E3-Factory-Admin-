@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Add Coach</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Coach</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.coaches.store') }}" method="POST" autocomplete="off"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Coach Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="participant_id" class="form-label">Participants </label>
                                    <select name="participant_id" id="participant_id" class="" required>
                                        <option label="Select"></option>
                                        @foreach ($participants as $participant)
                                            <option value="{{ $participant->id }}">{{ $participant->first_name }}
                                                {{ $participant->last_name }} ({{ $participant->mobile }})</option>
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
        $(document).ready(function() {
            $('#batch_id, #participant_id').select2({
                placeholder: 'Select',
                width: '100%',
                theme: 'bootstrap4',
                allowClear: true
            });

        });
    </script>
@endsection
