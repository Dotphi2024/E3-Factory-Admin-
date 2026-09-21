@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Edit Photo</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Photo</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.notice-and-announcement.update', $notice_and_announcement->id) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">News/Announcement Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="type" class="form-label">Type </label>
                                    <select name="type" id="type" class="form-select" required>
                                        <option value="notice" {{ $notice_and_announcement->type == 'notice' ? 'selected' : ''}}>Notice</option>
                                        <option value="announcement" {{ $notice_and_announcement->type == 'announcement' ? 'selected' : ''}}>Announcement</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Title </label>
                                    <input type="text" class="form-control" id="title"
                                        value="{{ $notice_and_announcement->title }}" name="title" placeholder="Enter Title"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Description </label>
                                    <textarea name="description" id="description" class="form-control">{{ $notice_and_announcement->description }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Select Image</label>
                                    <input type="file" class="form-control" id="image" name="image"
                                        {{ !$notice_and_announcement->image ? 'required' : '' }}>
                                </div>

                                <div class="col-md-6">
                                    <label for="visibility" class="form-label">Visibility</label>
                                    <select name="visibility" id="visibility" class="form-select">
                                        <option label="Select"></option>
                                        <option value="Guest"
                                            {{ $notice_and_announcement->visibility == 'Guest' ? 'selected' : '' }}>Guest</option>
                                        <option value="Batch"
                                            {{ $notice_and_announcement->visibility == 'Batch' ? 'selected' : '' }}>Batch</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="batch-id-div" style="display: none">
                                    <label for="batch_id" class="form-label">Select Batch</label>
                                    <select name="batch_id" id="batch_id" class="single-select">
                                        <option label="Select"></option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->id }}"
                                                {{ $batch->id == $notice_and_announcement->batch_id ? 'selected' : '' }}>
                                                {{ $batch->name }}</option>
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
            showHideBatch('{{ $notice_and_announcement->visibility }}');
        })
        $(document).on('change', '#visibility', function() {
            showHideBatch($(this).val());
        });

        function showHideBatch(value) {
            if (value == 'Batch') {
                $('#batch-id-div').show();
                $('#batch_id').prop('required', true);
            } else {
                $('#batch-id-div').hide();
                $('#batch_id').prop('required', false);
            }
        }
    </script>
@endsection
