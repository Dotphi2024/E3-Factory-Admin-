@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Session Details - {{ $batch_schedule->name }}</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Session Details - {{ $batch_schedule->name }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card radius-10 bg-transparent shadow-none w-100">
        <div class="card-body p-0">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-light-danger text-danger">
                            <ion-icon name="people-sharp" role="img" class="md hydrated"
                                aria-label="people sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto">
                            <ion-icon name="ellipsis-horizontal-sharp" role="img" class="md hydrated"
                                aria-label="ellipsis horizontal sharp"></ion-icon>
                        </div>
                    </div>
                    <h4 class="my-3">{{ $batch_schedule->batch ? $batch_schedule->batch->batchParticipants->count() : 0 }}</h4>
                    <p class="mb-0 mt-2">No Of Participants</p>
                </div>
            </div>
            <div class="card radius-10 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon-2 bg-light-danger text-danger">
                            <ion-icon name="people-sharp" role="img" class="md hydrated"
                                aria-label="people sharp"></ion-icon>
                        </div>
                        <div class="fs-5 ms-auto">
                            <ion-icon name="ellipsis-horizontal-sharp" role="img" class="md hydrated"
                                aria-label="ellipsis horizontal sharp"></ion-icon>
                        </div>
                    </div>
                    <h4 class="my-3">{{$participants_reached}}</h4>
                    <p class="mb-0 mt-2">Participants Reached</p>
                </div>
            </div>
        </div>
    </div>
@endsection
