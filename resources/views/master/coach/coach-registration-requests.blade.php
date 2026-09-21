@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Coach Registration Requests</h6>
    <hr />
    <div class="card">
        <div class="card-body">

            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name </th>
                            <th>Batch Name</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coaches as $key => $coach)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $coach->participant ? $coach->participant->first_name . ' ' . $coach->participant->last_name : '' }}</td>
                                <td>
                                    <a href="{{route('master.batches.view',$coach->batch->id)}}">{{ $coach->batch->name }}</a>
                                </td>
                                <td>{{ ucfirst($coach->status) }}</td>
                                <td>{{ $coach->addedBy ? $coach->addedBy->name : 'Self Registered' }}</td>
                                <td>{{ $coach->created_at->format('d-m-Y') }}</td>

                                <td>
                                    @if ($coach->status == 'pending')
                                        <form action="{{route('master.coaches.update-registration-request-status')}}" method="post" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $coach->id }}">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </form>

                                        <form action="{{route('master.coaches.update-registration-request-status')}}" method="post" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $coach->id }}">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </form>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
