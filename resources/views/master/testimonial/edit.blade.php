@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Edit Testimonial</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Testimonial</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.testimonials.update', $testimonial->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Testimonial Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Title </label>
                                    <input type="text" class="form-control" id="title" value="{{ $testimonial->title }}"
                                        name="title" placeholder="Enter Title" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Description </label>
                                    <textarea name="description" id="description" class="form-control">{{ $testimonial->description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Select Image</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                </div>
                                <div class="col-md-6">
                                    <label for="video_url" class="form-label">Video Url</label>
                                    <input type="text" class="form-control" id="video_url" name="video_url" value="{{ $testimonial->video_url }}">
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
