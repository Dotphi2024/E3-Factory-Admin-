@extends('master.layout.layout')
@section('main_content')
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Assignment List Of {{ $batch_schedule->name }} Session</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0 align-items-center">
                    <li class="breadcrumb-item"><a href="javascript:;"><ion-icon name="home-outline"></ion-icon></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Participant
                        <a href="{{ route('master.participants.view', $participant->id) }}">{{ $participant->first_name }}
                            {{ $participant->last_name }}</a>
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-12 col-lg-12 mx-auto">
            <div class="text-center">
                <h5 class="mb-0 text-uppercase">Assignments</h5>
                <hr>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="accordion" id="accordionExample">
                        @if ($batch_schedule->assignments->count() > 0)
                            @foreach ($batch_schedule->assignments as $key => $assignment)
                                @php
                                    $assignment_submission = $assignment->submissions
                                        ->where('participant_id', $participant->id)
                                        ->first();
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $key }}" aria-expanded="false"
                                            aria-controls="collapseOne">
                                            {{ $assignment->question }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $key }}" class="accordion-collapse collapse"
                                        aria-labelledby="headingOne" data-bs-parent="#accordionExample" style="">
                                        <div class="accordion-body">
                                            @if ($assignment_submission && $assignment->option_type == 'manual')
                                                <p>{{ $assignment_submission->answer }}</p>
                                            @endif
                                            @if ($assignment->option_type == 'options')
                                                @foreach ($assignment->options as $key => $assignment_option)
                                                    <p>{{ $key + 1 }}: <strong>{{ $assignment_option->option }} (Marks:
                                                            {{ $assignment_option->mark }})</strong>
                                                    </p>
                                                @endforeach
                                            @endif
                                            @if ($assignment_submission && $assignment->option_type == 'options' && $assignment_submission->assignmentOption)
                                                <p>Participant Response:
                                                    <strong>{{ $assignment_submission->assignmentOption->option }}</strong>
                                                </p>
                                            @endif
                                            @if ($assignment_submission && $assignment->is_file_upload && $assignment_submission->file)
                                                <a href="{{ asset('uploads/participants/assignment/' . $assignment_submission->file) }}"
                                                    class="btn btn-primary" target="_blank">View File</a>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-center"><strong> Assignment Found </strong></p>
                        @endif
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $batch_schedule->assignments->count() + 1 }}"
                                    aria-expanded="false" aria-controls="collapseOne">
                                    How many Offline group meetings you have attended?
                                </button>
                            </h2>
                            <div id="collapse{{ $batch_schedule->assignments->count() + 1 }}"
                                class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample" style="">
                                <div class="accordion-body">
                                    <p>Formula: <strong>(Total meetings attended)/ (Total meetings cunducted) *100</strong>
                                    </p>
                                    <p>Marks Obtained:
                                        <strong>{{ $offline_meetings->count() }} / {{ $offline_meetings_attended }} x 100
                                            = {{ $offline_meeting_attended_marks }} </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $batch_schedule->assignments->count() + 2 }}"
                                    aria-expanded="false" aria-controls="collapseOne">
                                    How many Online group meetings you have attended?
                                </button>
                            </h2>
                            <div id="collapse{{ $batch_schedule->assignments->count() + 2 }}"
                                class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample" style="">
                                <div class="accordion-body">
                                    <p>Formula: <strong>(Total meetings attended)/ (Total meetings cunducted) *100</strong>
                                    </p>
                                    <p>Marks Obtained:
                                        <strong>{{ $online_meetings->count() }} / {{ $online_meetings_attended }} x 100 =
                                            {{ $online_meeting_attended_marks }} </strong>
                                    </p>

                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse{{ $batch_schedule->assignments->count() + 3 }}"
                                    aria-expanded="false" aria-controls="collapseOne">
                                    Coach Rating
                                </button>
                            </h2>
                            <div id="collapse{{ $batch_schedule->assignments->count() + 3 }}"
                                class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample" style="">
                                <div class="accordion-body">
                                    <p>Formula: <strong>On the scale of 1 to 10 (convert it in to 100)</strong></p>
                                    <p>Marks Obtained:
                                        <strong>{{ $rating ? $rating->rating : 0 }}  x 10 =
                                            {{ $rating ? $rating->rating * 10 : 0 }} </strong>
                                    </p>

                                </div>
                            </div>
                        </div>
                        @if ($next_session)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ $batch_schedule->assignments->count() + 4 }}"
                                        aria-expanded="false" aria-controls="collapseOne">
                                        Registration For Next Session?
                                    </button>
                                </h2>
                                <div id="collapse{{ $batch_schedule->assignments->count() + 4 }}"
                                    class="accordion-collapse collapse" aria-labelledby="headingOne"
                                    data-bs-parent="#accordionExample" style="">
                                    <div class="accordion-body">
                                        <p>Response:<strong>{{ $is_registered_for_next_session ? 'Yes' : 'No' }}</strong>
                                        </p>
                                        <p>Marks Obtained:
                                            <strong>{{ $is_registered_for_next_session ? '100 Marks' : '0 Marks' }}
                                            </strong>
                                        </p>

                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <p class="text-center mt-3">Total Marks :
                        <strong>{{ $total_marks_obtained }}/{{ $total_out_of_marks }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
