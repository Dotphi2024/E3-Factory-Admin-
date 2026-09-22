@extends('master.layout.layout')
@section('main_content')
    <div class="row">
        <div class="col-12 col-lg-8 col-xl-9">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-0 text-uppercase">Coaches List</h6>
                    <div style="float: right;" class="d-flex gap-2">
                        <a href="{{ route('master.coaches.export') }}" class="btn btn-success px-4">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Coaches CSV
                        </a>
                        @can('batch add')
                            <a href="{{ route('master.coaches.add') }}">
                                <button type="button" class="btn btn-primary px-4">Add New Coach</button>
                            </a>
                        @endcan
                    </div>
                    <br><br>
                    <hr>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>
                                    <th>Batches</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($participants as $key => $participant)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $participant->first_name . ' ' . $participant->last_name }}</td>
                                        <td>
                                            @if ($participant->batches->count() > 0)
                                                @foreach ($participant->batches as $batch)
                                                    <a
                                                        href="{{ route('master.batches.view', $batch->id) }}">{{ $batch->name }}</a>
                                                    <br>
                                                @endforeach
                                            @else
                                                {{ 'No Service' }}
                                            @endif
                                        </td>


                                        <td>

                                            @can('participant edit')
                                                <button data-bs-target="#assign-batch-modal" data-bs-toggle="modal"
                                                    data-id="{{ $participant->id }}"
                                                    class="btn btn-sm btn-primary assign-batch-btn">Assign Batch</button>

                                                &nbsp;&nbsp;
                                            @endcan


                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-xl-3">
            <div class="card radius-10">
                <div class="card-body">
                    <h5 class="mb-3">Stats</h5>
                    <h6 class="">Total Coaches: {{ $participants->count() }}</h6>
                    <h6 class="">Active Coaches: {{ $participants->where('is_active', 1)->count() }}</h6>
                    <a href="{{route('master.coaches.coach-registration-requests')}}" class="btn btn-primary">Coach Registrations Requests</a>
                </div>
            </div>
            <div class="card radius-10">
                <div class="card-body">
                    <h5 class="mb-3">Batches Accepting Coach Registrations</h5>
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name </th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batches as $key => $batch)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a href="{{ route('master.batches.view', $batch->id) }}">{{ $batch->name }}</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Assign Batch Modal --}}
    <form action="{{ route('master.coaches.assign-batch') }}" method="post" enctype="multipart/form-data"
        id="start-registration-form">
        @csrf
        <div class="modal fade" tabindex="-1" id="assign-batch-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Batch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group m-2">
                            <label for="batch_id" class="form-label">Select Batch </label>
                            <select name="batch_id" id="batch_id" class="form-control" required>
                                <option label="Select"></option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="participant_id" id="participant_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End  --}}
@endsection
@section('custom_js')
    <script>
        $(document).on('click', '.assign-batch-btn', function() {
            $('#participant_id').val($(this).data('id'));
        })
    </script>
@endsection
