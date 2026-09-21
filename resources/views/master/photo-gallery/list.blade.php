@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Photo Gallery List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('participant add')
                <a href="{{ route('master.photo-gallery.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add New Photo</button>
                    </div>
                </a>
            @endcan
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Title </th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Visibility</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($photo_galleries as $key => $photo_gallery)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $photo_gallery->title }}</td>
                                <td>
                                    <textarea class="form-control" disabled>{{ $photo_gallery->description }}</textarea>
                                </td>
                                <td><a href="{{ asset('uploads/photo-galleries/'.$photo_gallery->image) }}" target="_blank"><img src="{{ asset('uploads/photo-galleries/'.$photo_gallery->image) }}" alt="no-image" width="80px"></a></td>
                                <td>{{ $photo_gallery->visibility }}</td>
                                <td>{{ $photo_gallery->batch ? $photo_gallery->batch->name : '' }}</td>
                                <td>

                                    @if ($photo_gallery->is_active)
                                        <a href="{{ route('master.photo-gallery.update-active-status', $photo_gallery->id) }}"><span
                                                class="badge bg-success">Active</span></a>
                                    @else
                                        <a href="{{ route('master.photo-gallery.update-active-status', $photo_gallery->id) }}"><span
                                                class="badge bg-danger">Inactive</a>
                                    @endif
                                </td>

                                <td>{{ $photo_gallery->addedBy ? $photo_gallery->addedBy->name : '' }}</td>
                                <td>{{ $photo_gallery->created_at->format('d-m-Y') }}</td>
                                <td>

                                    @can('photo-gallery edit')
                                        <a href="{{ route('master.photo-gallery.edit', $photo_gallery->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    @can('photo-gallery delete')
                                        <a href="{{ route('master.photo-gallery.delete', $photo_gallery->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-delete">
                                                <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"></path>
                                                <line x1="18" y1="9" x2="12" y2="15"></line>
                                                <line x1="12" y1="9" x2="18" y2="15"></line>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
