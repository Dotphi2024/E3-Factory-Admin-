@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Add Video</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Video</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.video-gallery.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Video Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Title </label>
                                    <input type="text" class="form-control" id="title" value="{{ old('title') }}"
                                        name="title" placeholder="Enter Title" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Description </label>
                                    <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="video_url" class="form-label">Video Url</label>
                                    <input type="text" class="form-control" id="video_url" name="video_url" placeholder="Enter Url" value="{{ old('video_url') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="visibility" class="form-label">Visibility</label>
                                    <select name="visibility" id="visibility" class="form-select" required>
                                        <option label="Select"></option>
                                        <option value="Guest">Guest</option>
                                        <option value="Batch">Batch</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="batch-id-div" style="display: none">
                                    <label for="batch_id" class="form-label">Select Batch</label>
                                    <select name="batch_id" id="batch_id" class="single-select">
                                        <option label="Select"></option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->id }}">{{ $batch->name }}</option>
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
        $(document).on('change', '#visibility', function() {
            if ($(this).val() == 'Batch') {
                $('#batch-id-div').show();
                $('#batch_id').prop('required', true);
            } else {
                $('#batch-id-div').hide();
                $('#batch_id').prop('required', false);
            }
        })
    </script>
@endsection
