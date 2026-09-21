@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dashboard</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Batch</th>
                            <th>Session Name</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($batch_schedules as $batch_schedule)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $batch_schedule->batch ? $batch_schedule->batch->name : '' }}</td>
                                <td>{{ $batch_schedule->name }}</td>
                                <td>{{ $batch_schedule->date }}</td>
                                <td>
                                    <a href="{{route('entry-user.session-details',$batch_schedule->id)}}" class="btn btn-primary">View Details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--end breadcrumb-->
@endsection
