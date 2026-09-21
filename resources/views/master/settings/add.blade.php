@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Settings</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Setting</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->


    <div class="row">
        <div class="col-xl-12 mx-auto">
            <h6 class="mb-0 text-uppercase">Source Information</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    <div class="p-4 border rounded">
                        <form class="row g-3 needs-validation" action="{{ route('master.setting.store-setting') }}"
                            method="POST">
                            @csrf
                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="is_gst" class="form-label">Is Gst enabled</label>
                                    <input type="checkbox" name="is_gst" class="form-check-input" id="is_gst"
                                        value="{{ \App\Models\Setting::getValByKey('is_gst') == 1 ? 0 : 1 }}"
                                        {{ \App\Models\Setting::getValByKey('is_gst') == 1 ? 'checked' : '' }}>
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
