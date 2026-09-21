@extends('master.layout.layout')
@section('main_content')
    <h6 class="mb-0 text-uppercase">Leads List</h6>
    <hr />
    <div class="card">
        <div class="card-body">

            @can('recommendations add')
                <a href="{{ route('master.recommendations.add') }}">
                    <div class="col"style="float: right">
                        <button type="button" class="btn btn-primary px-5">Add Lead</button>
                    </div>
                </a>
            @endcan
            <br><br>
            <hr>
            <div class="table-responsive">
                <table id="example" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Lead Name</th>
                            <th>Mobile</th>
                            <th>Follow Up</th>
                            <th>Created At</th>
                            <th>Participant Name(Recommender) </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recommendations as $key => $recommendation)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $recommendation->name }}</td>
                                <td>{{ $recommendation->mobile }}</td>
                                <td>{{ $recommendation->follow_up }}</td>
                                <td>{{ $recommendation->created_at->format('d-m-Y') }}</td>
                                <td>{{ $recommendation->participant ? $recommendation->participant->first_name . ' ' . $recommendation->participant->last_name : '' }}
                                </td>
                                <td>

                                    @can('recommendations edit')
                                        @if ($recommendation->follow_up == 'Pending' || $recommendation->follow_up == 'InProgress')
                                            <a href="{{ route('master.recommendations.update-follow-up-status', $recommendation->id) }}?status=Completed"
                                                title="Mark Completed">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-check">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                            </a>
                                            &nbsp;&nbsp;
                                            <a href="{{ route('master.recommendations.update-follow-up-status', $recommendation->id) }}?status=InProgress"
                                                title="Mark InProgress">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-arrow-up-right">
                                                    <line x1="7" y1="17" x2="17" y2="7"></line>
                                                    <polyline points="7 7 17 7 17 17"></polyline>
                                                </svg>
                                            </a>
                                            &nbsp;&nbsp;
                                            <a href="{{ route('master.recommendations.update-follow-up-status', $recommendation->id) }}?status=Failed"
                                                title="Mark Failed">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                            </a>
                                            &nbsp;&nbsp;
                                        @endif
                                        <a href="javascript:void(0)" title="Add Follow Up" data-bs-toggle="modal"
                                            data-bs-target="#add-follow-up-modal" class="add-follow-up-btn"
                                            data-id="{{ $recommendation->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-plus-circle">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                            </svg>
                                        </a>
                                        &nbsp;&nbsp;
                                        <a href="javascript:void(0)" title="View Follow Ups" data-bs-toggle="modal"
                                            data-bs-target="#view-follow-up-modal" class="view-follow-up-btn"
                                            data-follow-ups="{{ $recommendation->followUps }}"
                                            data-mobile="{{ $recommendation->mobile }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
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

    {{-- Add Follow Up Modal --}}
    <form action="{{ route('master.recommendations.add-follow-up') }}" method="post">
        @csrf
        <div class="modal fade" id="add-follow-up-modal" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add Follow Up</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date"
                                value="{{ old('date') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" name="time"
                                value="{{ old('time') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="log" class="form-label">Log</label>
                            <textarea name="log" id="log" class="form-control" required>{{ old('log') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="recommendation_id" id="recommendation_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- End Add Follow Up Modal --}}

    {{-- View Follow Up Modal --}}
    <div class="modal fade" id="view-follow-up-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Follow Ups of <span id="recommendation-name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="logs-div">

                        {{-- <label class="form-label">Date</label>
                        <p id="date">2022-01-01</p>
                        <label class="form-label">Time</label>
                        <p id="time"> 12:00 AM</p>
                        <label class="form-label">Log</label>
                        <p id="log"> Log</p>
                        <label for="" class="form-label">Added By</label>
                        <hr> --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- End View Follow Up Modal --}}
@endsection
@section('custom_js')
    <script>
        $(document).on('click', '.add-follow-up-btn', function() {
            $('#recommendation_id').val($(this).data('id'));
        });
        $(document).on('click', '.view-follow-up-btn', function() {
            var follow_ups = $(this).data('follow-ups');
            $('.logs-div').empty();
            $.each(follow_ups, function(key, value) {


                $('.logs-div').append(`
                    <label class="form-label">Date:</label>
                    <b>` + value.date + `</b>
                    <br>
                    <label class="form-label">Time:</label>
                    <b>` + value.time + `</b>
                    <br>
                    <label class="form-label">Added By:</label>
                    <b>` + value.added_by.name + `</b>
                    <br>
                    <label class="form-label">Log:</label>
                    <b>` + value.log + `</b>
                    <hr>
                `)
            });
            $('#recommendation-name').text($(this).data('mobile'));
        })
    </script>
@endsection
