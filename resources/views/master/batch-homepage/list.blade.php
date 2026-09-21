@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Batch Homepage Content List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('batch-homepage add')
                <a href="{{ route('master.batch-homepage.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add New Batch Homepage Content</button>
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
                            <th>Batch</th>
                            <th>Title </th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Url</th>
                            <th>Video Url</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Sequence</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guest_homepage_data as $key => $guest_home_page)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($guest_home_page->batch)
                                        <a
                                            href="{{ route('master.batches.view', $guest_home_page->batch_id) }}">{{ $guest_home_page->batch->name }}</a>
                                    @endif
                                </td>
                                <td>{{ $guest_home_page->title }}</td>
                                <td>
                                    <textarea class="form-control" disabled>{{ $guest_home_page->description }}</textarea>
                                </td>
                                <td>
                                    @if ($guest_home_page->image)
                                        <a href="{{ asset('uploads/guest-homepage/' . $guest_home_page->image) }}"
                                            target="_blank"><img
                                                src="{{ asset('uploads/guest-homepage/' . $guest_home_page->image) }}"
                                                alt="no-image" width="80px">
                                        </a>
                                    @endif
                                </td>

                                <td>{{ $guest_home_page->url }}</td>
                                <td>{{ $guest_home_page->video_url }}</td>
                                <td>

                                    @if ($guest_home_page->is_active)
                                        <a
                                            href="{{ route('master.batch-homepage.update-active-status', $guest_home_page->id) }}"><span
                                                class="badge bg-success">Active</span></a>
                                    @else
                                        <a
                                            href="{{ route('master.batch-homepage.update-active-status', $guest_home_page->id) }}"><span
                                                class="badge bg-danger">Inactive</a>
                                    @endif
                                </td>

                                <td>{{ $guest_home_page->addedBy ? $guest_home_page->addedBy->name : '' }}</td>
                                <td>{{ $guest_home_page->created_at->format('d-m-Y') }}</td>
                                <td>{{ $guest_home_page->sequence }}</td>
                                <td>

                                    @can('batch-homepage edit')
                                        <a href="{{ route('master.batch-homepage.edit', $guest_home_page->id) }}"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                    @endcan
                                    @can('batch-homepage delete')
                                        <a href="{{ route('master.batch-homepage.delete', $guest_home_page->id) }}"><svg
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
