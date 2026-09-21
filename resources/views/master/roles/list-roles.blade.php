@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">View Roles List</h6>
    <hr />
    <div class="card">
        <div class="card-body">
            @can('roles add')
                <div class="col"style="float: right">
                    <a href="{{ route('master.roles.add') }}" class="btn btn-primary px-5">Add New Roles</a>
                </div>
            @endcan
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Roles</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>
                                    <div class="mb-3">
                                        <input type="text" class="form-control"
                                            value="{{ $role->permissions->pluck('name')->implode(', ') }}" disabled>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3 fs-6">
                                        @can('roles edit')
                                            <a href="{{ route('master.roles.edit', $role->id) }}" class="text-warning"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                                data-bs-original-title="Edit" aria-label="Edit"><ion-icon
                                                    name="pencil-sharp"></ion-icon></a>
                                        @endcan
                                        @can('roles delete')
                                            <a href="{{ route('master.roles.delete', $role->id) }}" class="text-danger"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title=""
                                                data-bs-original-title="Delete" aria-label="Delete"><ion-icon
                                                    name="trash-sharp"></ion-icon></a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
