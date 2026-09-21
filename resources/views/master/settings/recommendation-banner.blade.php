@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Recommendation Banner</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Add Recommendation Banner</li>
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
                        <form class="row g-3 needs-validation" action="{{ route('master.setting.update-recommendation-banner') }}" enctype="multipart/form-data"
                            method="POST">
                            @csrf
                            <div class="row g-3 needs-validation">
                                <div class="col-md-12">
                                    <label for="image" class="form-label">Select Image</label>
                                    <input type="file" name="image" id="image" class="form-control" {{!$recommendation_banner ? 'required' : ''}}>
                                    @if($recommendation_banner)
                                        <img src="{{ asset('uploads/recommendation-banner/'.$recommendation_banner) }}" alt="" width="100" class="mt-2">
                                    @endif
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
