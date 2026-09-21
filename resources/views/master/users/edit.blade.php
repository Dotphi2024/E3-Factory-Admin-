@extends('master.layout.layout')
@section('main_content')
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Edit User</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}"><ion-icon
                                name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                </ol>
            </nav>
        </div>
    </div>
    <!--end breadcrumb-->

    <form class="row g-3 needs-validation" action="{{ route('master.users.update', $user->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-xl-12 mx-auto"><br>
                <h6 class="mb-0 text-uppercase">User Information</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="p-4 border rounded">

                            <div class="row g-3 needs-validation">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name </label>
                                    <input type="text" class="form-control" id="name" value="{{ $user->name }}"
                                        name="name" placeholder="Center Name" required>
                                    <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="{{ $user->email }}" placeholder="Email" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" placeholder="Leave it blank if you don't want to change" name="password"
                                            id="password" aria-label="Password" aria-describedby="basic-addon2">
                                        <span class="input-group-text" style="cursor: pointer" id="show-password">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" id="show-icon"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-eye-off" id="icon-id">
                                                <path
                                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24">
                                                </path>
                                                <line x1="1" y1="1" x2="23" y2="23"></line>
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" style="display: none" id="hide-icon"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="user_type" class="form-label">User type </label>
                                    <select name="user_type" id="user_type" class="form-select" required>
                                        <option label="Select User type"></option>
                                       <option value="staff" {{ $user->user_type == 'staff' ? 'selected' : '' }}>Staff</option>
                                       <option value="entry-user" {{ $user->user_type == 'entry-user' ? 'selected' : '' }}>Entry User</option>
                                    </select>
                                </div>
                                <div class="col-md-6 role-div" @if($user->user_type == 'entry-user') style="display: none" @endif>
                                    <label for="role_id" class="form-label">Role </label>
                                    <select name="role_id" id="role_id" class="form-select" {{ $user->user_type == 'staff' ? 'required' : ''}}>
                                        <option label="Select Role"></option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" {{ optional($user->roles->first())->id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </form>
    <!--end row-->
@endsection
@section('custom_js')
    <script>
        $(document).on('click', '#show-password', function() {
            if ($('#password').attr('type') == 'password') {
                $('#password').attr('type', 'text');
                $('#show-icon').hide();
                $('#hide-icon').show();
            } else {
                $('#password').attr('type', 'password');
                $('#icon-id').addClass('feather-eye');
                $('#show-icon').show();
                $('#hide-icon').hide();
            }
        });
        $(document).on('change', '#user_type', function() {
            if ($(this).val() == 'staff') {
                $('.role-div').show();
                $('#role_id').prop('required', true);
            } else {
                $('.role-div').hide();
                $('#role_id').prop('required', false);
            }
        });
    </script>
@endsection
