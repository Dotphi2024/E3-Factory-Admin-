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
                    <li class="breadcrumb-item active" aria-current="page">Admin Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->


    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 row-cols-xxl-4">
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Active Batches</p>
                            <h4 class="mb-0 text-primary">{{ $batches->where('is_active', 1)->count() }}</h4>
                        </div>
                        <div class="ms-auto text-primary fs-2">
                            <ion-icon name="bag-handle-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Courses</p>
                            <h4 class="mb-0 text-danger">{{ $courses->count() }}</h4>
                        </div>
                        <div class="ms-auto text-danger fs-2">
                            <ion-icon name="pie-chart-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Users</p>
                            <h4 class="mb-0 text-success">{{ $users->count() }}</h4>
                        </div>
                        <div class="ms-auto text-success fs-2">
                            <ion-icon name="wallet-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Participants</p>
                            <h4 class="mb-0 text-info">{{ $participants->count() }}</h4>
                        </div>
                        <div class="ms-auto text-info fs-2">
                            <ion-icon name="people-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="">
                            <p class="mb-1">Total Coaches</p>
                            <h4 class="mb-0 text-tiffany">{{ $participants->where('is_coach', 1)->count() }}</h4>
                        </div>
                        <div class="ms-auto widget-icon bg-light-tiffany text-tiffany">
                            <ion-icon name="chatbubbles-sharp"></ion-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!--end row-->
@endsection
