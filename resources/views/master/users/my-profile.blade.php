@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">My Profile</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card radius-10">
                <div class="card-body">
                    <form action="{{ route('master.my-profile.update') }}" method="post" autocomplete="off">
                        @csrf
                        <h5 class="mb-0 mt-4">User Information</h5>
                        <hr>
                        <div class="row g-3">
                            <div class="col-6">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="{{Auth::user()->name}}" required>
                            </div>
                            <div class="col-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="{{Auth::user()->email}}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank if you don't want to change">
                            </div>
                        </div>


                        <div class="text-start mt-3">
                            <button type="submit" class="btn btn-primary px-4">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
