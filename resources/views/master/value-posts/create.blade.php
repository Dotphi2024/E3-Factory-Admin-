@extends('master.layout.layout')
@section('main_content')
    <div class="row">
        <div class="col-xl-8 mx-auto">
            <h6 class="mb-0 text-uppercase">Add Value Post</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('master.value-posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Post Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="Enter post title" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Post Message (WhatsApp Content) <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" placeholder="Enter message to send via WhatsApp to all active participants..." required>{{ old('message') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Post Image (Optional)</label>
                            <input class="form-control" type="file" id="image" name="image" accept="image/*">
                        </div>

                        <div class="mb-3 p-3 border rounded bg-light">
                            <label class="form-label fw-bold d-block mb-2">Publishing Action <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="post_type" id="post_type_now" value="now" {{ empty($presetDate) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-success" for="post_type_now">
                                    <i class="bi bi-whatsapp me-1"></i> Send Immediately via WhatsApp ({{ $activeParticipantCount }} Active Participants)
                                </label>
                            </div>
                            <div class="form-check form-check-inline ms-3">
                                <input class="form-check-input" type="radio" name="post_type" id="post_type_schedule" value="schedule" {{ !empty($presetDate) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-primary" for="post_type_schedule">
                                    <i class="bi bi-calendar-event me-1"></i> Schedule for Later
                                </label>
                            </div>

                            <div class="mt-3" id="schedule_datetime_div" style="{{ !empty($presetDate) ? '' : 'display: none;' }}">
                                <label for="scheduled_at" class="form-label fw-semibold text-dark">Schedule Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control border-primary" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at', $presetDate) }}">
                                <small class="text-muted font-12">The post will automatically broadcast via WhatsApp on this scheduled date when the daily cron runs.</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('master.value-posts.list') }}" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-success px-5" id="submit_post_btn">
                                <i class="bi bi-whatsapp me-2"></i> <span id="submit_btn_text">{{ !empty($presetDate) ? 'Schedule Value Post' : 'Save & Send WhatsApp' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
    <script>
        $(document).ready(function() {
            function toggleScheduleInput() {
                if ($('#post_type_schedule').is(':checked')) {
                    $('#schedule_datetime_div').slideDown(200);
                    $('#scheduled_at').prop('required', true);
                    $('#submit_btn_text').text('Schedule Value Post');
                    $('#submit_post_btn').removeClass('btn-success').addClass('btn-primary');
                } else {
                    $('#schedule_datetime_div').slideUp(200);
                    $('#scheduled_at').prop('required', false);
                    $('#submit_btn_text').text('Save & Send WhatsApp');
                    $('#submit_post_btn').removeClass('btn-primary').addClass('btn-success');
                }
            }

            $('input[name="post_type"]').on('change', toggleScheduleInput);
            toggleScheduleInput();
        });
    </script>
@endsection
