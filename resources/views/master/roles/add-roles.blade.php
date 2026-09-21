@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Add Roles</h6>
    <hr />
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Add Roles</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item">
                        <a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Add
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="border p-3 rounded">
                        <form class="row g-3" action="{{ route('master.roles.store') }}" method="POST">
                            @csrf
                            <div class="col-md-12">
                                <label for="name" class="form-label">Role Name</label>
                                <input type="text" class="form-control" id="name" value="{{ old('name') }}"
                                    name="name" placeholder="Roles" required>
                            </div>
                            <div class="row mt-3">
                                <label for="permission" class="col-xs-5 control-label">Module Permission:</label>
                            </div>
                            <div class="row">
                                @foreach ($modules as $row)
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <br>
                                            <label for="permission"
                                                class="col-5 form-label font-weight-bold">{{ ucfirst($row) }}</label><br>
                                            <div class="d-flex">

                                                <input type="checkbox" name="{{ $row . '_list' }}" value="1"
                                                    class="form-check">&nbsp; List &nbsp;
                                                &nbsp;&nbsp;&nbsp;


                                                <input type="checkbox" name="{{ $row . '_add' }}" value="1"
                                                    class="form-check">&nbsp; Add &nbsp; &nbsp;&nbsp;&nbsp;


                                                <input type="checkbox" name="{{ $row . '_edit' }}" value="1"
                                                    class="form-check">&nbsp; Edit &nbsp;
                                                &nbsp;&nbsp;&nbsp;


                                                <input type="checkbox" name="{{ $row . '_delete' }}" value="1"
                                                    class="form-check">&nbsp; Delete &nbsp;
                                                &nbsp;&nbsp;&nbsp;


                                                <input type="checkbox" name="{{ $row . '_view' }}" value="1"
                                                    class="form-check">&nbsp; View &nbsp; &nbsp;&nbsp;&nbsp;
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-12">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>


    {{-- add roles --}}
    <div class="col">
        <!-- Modal -->
        <div class="modal fade" id="exampleLargeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Roles</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-header">
                        <div class="col-md-12">
                            <input type="text" class="form-control" id="first_name" value="" name=""
                                placeholder="Roles">
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">
                                <h6 class="modal-title">User Module</h6>
                            </label>
                        </div>
                        <hr>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">Manage User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Create User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">Edit User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Delete User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">Manage Role</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Create Role</label>
                        </div>
                        <hr>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">
                                <h6 class="modal-title">patient Module</h6>
                            </label>
                        </div>
                        <hr>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">Manage User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Create User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">Edit User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Delete User</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">Manage Role</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Create Role</label>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Create</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- end add roles --}}
@endsection
