@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Get Help</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Get Help</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->


    <div class="row">
        <div class="col-xl-12 mx-auto">

            <div class="card">
                <div class="card-body">
                    <div class="p-4 border rounded">
                        <form class="row g-3 needs-validation" action="{{ route('master.setting.update-get-help') }}"
                            method="POST">
                            @csrf
                            <div class="row g-3 needs-validation">
                                <div class="col-md-12">
                                    <label for="get_help" class="form-label">Get Help</label>
                                    <textarea name="get_help" id="get_help" class="form-control" required>{{ $get_help }}</textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
