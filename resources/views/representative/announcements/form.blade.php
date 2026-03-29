@extends('layouts.auth')

@section('title', $announcement->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="{{ route('representative.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>{{ __('general.back') }}
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>{{ $announcement->title }}</h5>
                        <span class="badge bg-{{ $announcement->priority_color }}">{{ $announcement->priority_name }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($announcement->body)
                        <div class="bg-light rounded p-3 mb-4" style="white-space: pre-line;">{{ $announcement->body }}</div>
                    @endif

                    <form action="{{ route('representative.announcements.form.store', $announcement) }}" method="POST" enctype="multipart/form-data" id="formSubmission">
                        @csrf

                        @foreach($announcement->form_fields as $field)
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                {{ $field['label'] }}
                                @if($field['required']) <span class="text-danger">*</span> @endif
                            </label>

                            @if($field['type'] === 'text')
                                <input type="text" name="{{ $field['name'] }}"
                                       class="form-control @error($field['name']) is-invalid @enderror"
                                       value="{{ old($field['name']) }}"
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       {{ $field['required'] ? 'required' : '' }}>

                            @elseif($field['type'] === 'textarea')
                                <textarea name="{{ $field['name'] }}"
                                          class="form-control @error($field['name']) is-invalid @enderror"
                                          rows="4" placeholder="{{ $field['placeholder'] ?? '' }}"
                                          {{ $field['required'] ? 'required' : '' }}>{{ old($field['name']) }}</textarea>

                            @elseif($field['type'] === 'number')
                                <input type="number" name="{{ $field['name'] }}"
                                       class="form-control @error($field['name']) is-invalid @enderror"
                                       value="{{ old($field['name']) }}"
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       {{ $field['required'] ? 'required' : '' }}>

                            @elseif($field['type'] === 'email')
                                <input type="email" name="{{ $field['name'] }}"
                                       class="form-control @error($field['name']) is-invalid @enderror"
                                       value="{{ old($field['name']) }}"
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       {{ $field['required'] ? 'required' : '' }}>

                            @elseif($field['type'] === 'date')
                                <input type="date" name="{{ $field['name'] }}"
                                       class="form-control @error($field['name']) is-invalid @enderror"
                                       value="{{ old($field['name']) }}"
                                       {{ $field['required'] ? 'required' : '' }}>

                            @elseif($field['type'] === 'select')
                                <select name="{{ $field['name'] }}"
                                        class="form-select @error($field['name']) is-invalid @enderror"
                                        {{ $field['required'] ? 'required' : '' }}>
                                    <option value="">{{ $field['placeholder'] ?? '---' }}</option>
                                    @foreach($field['options'] ?? [] as $option)
                                        <option value="{{ $option }}" {{ old($field['name']) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>

                            @elseif($field['type'] === 'radio')
                                @foreach($field['options'] ?? [] as $option)
                                    <div class="form-check">
                                        <input class="form-check-input @error($field['name']) is-invalid @enderror"
                                               type="radio" name="{{ $field['name'] }}" value="{{ $option }}"
                                               id="{{ $field['name'] }}_{{ $loop->index }}"
                                               {{ old($field['name']) == $option ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $field['name'] }}_{{ $loop->index }}">{{ $option }}</label>
                                    </div>
                                @endforeach

                            @elseif($field['type'] === 'checkbox')
                                @foreach($field['options'] ?? [] as $option)
                                    <div class="form-check">
                                        <input class="form-check-input @error($field['name']) is-invalid @enderror"
                                               type="checkbox" name="{{ $field['name'] }}[]" value="{{ $option }}"
                                               id="{{ $field['name'] }}_{{ $loop->index }}"
                                               {{ is_array(old($field['name'])) && in_array($option, old($field['name'])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $field['name'] }}_{{ $loop->index }}">{{ $option }}</label>
                                    </div>
                                @endforeach

                            @elseif($field['type'] === 'file')
                                <input type="file" name="{{ $field['name'] }}"
                                       class="form-control @error($field['name']) is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                       {{ $field['required'] ? 'required' : '' }}>
                                <small class="text-muted">PDF, JPG, PNG, DOC, DOCX, XLS, XLSX ({{ __('general.max') }} 10MB)</small>
                            @endif

                            @error($field['name'])
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @endforeach

                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-1"></i>{{ __('announcements.submit_form') }}
                            </button>
                            <a href="{{ route('representative.dashboard') }}" class="btn btn-outline-secondary">{{ __('general.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('formSubmission').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;

    Swal.fire({
        title: '{{ __("announcements.form_submit_confirm") }}',
        text: '{{ __("announcements.form_submit_confirm_msg") }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1a5f4a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '{{ __("announcements.yes_submit") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>';
            form.submit();
        }
    });
});
</script>
@endpush
