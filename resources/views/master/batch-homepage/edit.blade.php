@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Edit Batch Homepage Content</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Batch Homepage Content</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.batch-homepage.update', $guest_home_page->id) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">Content Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="batch_id" class="form-label">Select Batch </label>
                                    <select name="batch_id" id="batch_id" class="form-select" required>
                                        <option label="Select"></option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->id }}"
                                                @if ($batch->id == $guest_home_page->batch_id) selected @endif>{{ $batch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Title </label>
                                    <input type="text" class="form-control" id="title"
                                        value="{{ $guest_home_page->title }}" name="title" placeholder="Enter Title">
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Description </label>
                                    <textarea name="description" id="description" class="form-control">{{ $guest_home_page->description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Select Image</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                    @if ($guest_home_page->image)
                                        <img src="{{ asset('uploads/guest-homepage/' . $guest_home_page->image) }}"
                                            alt="" width="100px" class="mt-2">
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="url" class="form-label">Url</label>
                                    <input type="text" class="form-control" id="url" name="url"
                                        value="{{ $guest_home_page->url }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="video_url" class="form-label">Video Url</label>
                                    <input type="text" class="form-control" id="video_url" name="video_url"
                                        value="{{ $guest_home_page->video_url }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="sequence" class="form-label">Sequence</label>
                                    <input type="number" class="form-control" id="sequence" name="sequence" value="{{ $guest_home_page->sequence }}">
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Update</button>
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
