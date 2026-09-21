@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Notice And Announcement List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            {{-- <div class="row">
                <form action="">
                    <div class="col-4">

                        <select name="" id="" class="form-select">
                            <option value="notice">Notice</option>
                            <option value="announcement">Announcement</option>
                        </select>
                        <button type="button" class="btn btn-outline-primary">Search</button>
                    </div>
                </form>
            </div> --}}
            @can('participant add')
                <a href="{{ route('master.notice-and-announcement.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add Notice/Announcement</button>
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
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notice_and_announcements as $key => $notice_and_announcement)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $notice_and_announcement->title }}</td>
                                <td>
                                    <textarea class="form-control" disabled>{{ $notice_and_announcement->description }}</textarea>
                                </td>
                                <td><a href="{{ asset('uploads/notice-and-announcement/' . $notice_and_announcement->image) }}"
                                        target="_blank"><img
                                            src="{{ asset('uploads/notice-and-announcement/' . $notice_and_announcement->image) }}"
                                            alt="no-image" width="80px"></a></td>
                                <td>{{ $notice_and_announcement->visibility }}</td>
                                <td>{{ $notice_and_announcement->batch ? $notice_and_announcement->batch->name : '' }}</td>

                                <td>{{ ucfirst($notice_and_announcement->type) }}</td>
                                <td>

                                    @if ($notice_and_announcement->is_active)
                                        <a
                                            href="{{ route('master.notice-and-announcement.update-active-status', $notice_and_announcement->id) }}"><span
                                                class="badge bg-success">Active</span></a>
                                    @else
                                        <a
                                            href="{{ route('master.notice-and-announcement.update-active-status', $notice_and_announcement->id) }}"><span
                                                class="badge bg-danger">Inactive</a>
                                    @endif
                                </td>

                                <td>{{ $notice_and_announcement->addedBy ? $notice_and_announcement->addedBy->name : '' }}
                                </td>
                                <td>{{ $notice_and_announcement->created_at->format('d-m-Y') }}</td>
                                <td>

                                    @can('photo-gallery edit')
                                        <a
                                            href="{{ route('master.notice-and-announcement.edit', $notice_and_announcement->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    @can('photo-gallery delete')
                                        <a
                                            href="{{ route('master.notice-and-announcement.delete', $notice_and_announcement->id) }}"><svg
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
