@extends('layouts.app')

@section('title', __('announcements.new_announcement'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.announcements.index') }}">{{ __('announcements.announcements') }}</a></li>
    <li class="breadcrumb-item active">{{ __('announcements.new_announcement') }}</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>{{ __('announcements.create_announcement') }}</h5>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>{{ __('general.back') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.announcements.store') }}" method="POST" id="announcementForm">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-bold">{{ __('announcements.type') }} <span class="text-danger">*</span></label>
                <div class="d-flex gap-3">
                    <div class="type-card {{ old('type', 'message') == 'message' ? 'active' : '' }}" data-value="message">
                        <input type="radio" name="type" value="message" id="typeMessage" class="d-none"
                               {{ old('type', 'message') == 'message' ? 'checked' : '' }}>
                        <i class="fas fa-envelope fa-lg"></i>
                        <span>{{ __('announcements.type_message') }}</span>
                    </div>
                    <div class="type-card {{ old('type') == 'form' ? 'active' : '' }}" data-value="form">
                        <input type="radio" name="type" value="form" id="typeForm" class="d-none"
                               {{ old('type') == 'form' ? 'checked' : '' }}>
                        <i class="fas fa-clipboard-list fa-lg"></i>
                        <span>{{ __('announcements.type_form') }}</span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">{{ __('announcements.announcement_title') }} <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="{{ __('announcements.announcement_title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label" id="bodyLabel">{{ __('announcements.announcement_body') }} <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control @error('body') is-invalid @enderror"
                          rows="5" placeholder="{{ __('announcements.body_placeholder') }}" required>{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="formBuilderSection" style="display: none;">
                <div class="form-builder-wrapper mb-4">
                    <div class="fb-header">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-tools me-2"></i>{{ __('announcements.form_builder') }}</h6>
                            <small class="text-muted" id="fieldCountText">0 {{ __('announcements.field_type') }}</small>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="addFieldBtn">
                            <i class="fas fa-plus me-1"></i>{{ __('announcements.add_field') }}
                        </button>
                    </div>

                    <div id="fieldsContainer" class="fb-fields">
                        <div class="fb-empty" id="noFieldsMsg">
                            <i class="fas fa-layer-group"></i>
                            <p>{{ __('announcements.no_fields') }}</p>
                        </div>
                    </div>

                    <div class="fb-add-bar" id="addFieldBar" style="display:none;">
                        <div class="fb-add-types">
                            <button type="button" class="fb-type-btn" data-type="text"><i class="fas fa-font"></i><span>{{ __('announcements.field_types.text') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="textarea"><i class="fas fa-align-left"></i><span>{{ __('announcements.field_types.textarea') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="number"><i class="fas fa-hashtag"></i><span>{{ __('announcements.field_types.number') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="email"><i class="fas fa-at"></i><span>{{ __('announcements.field_types.email') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="date"><i class="fas fa-calendar-alt"></i><span>{{ __('announcements.field_types.date') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="select"><i class="fas fa-caret-square-down"></i><span>{{ __('announcements.field_types.select') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="radio"><i class="fas fa-dot-circle"></i><span>{{ __('announcements.field_types.radio') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="checkbox"><i class="fas fa-check-square"></i><span>{{ __('announcements.field_types.checkbox') }}</span></button>
                            <button type="button" class="fb-type-btn" data-type="file"><i class="fas fa-paperclip"></i><span>{{ __('announcements.field_types.file') }}</span></button>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="form_fields" id="formFieldsInput">
                @error('form_fields')
                    <div class="text-danger mb-3">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('announcements.priority') }} <span class="text-danger">*</span></label>
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>{{ __('announcements.priority_normal') }}</option>
                        <option value="important" {{ old('priority') == 'important' ? 'selected' : '' }}>{{ __('announcements.priority_important') }}</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('announcements.priority_urgent') }}</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('announcements.target_audience') }} <span class="text-danger">*</span></label>
                    <select name="target" class="form-select @error('target') is-invalid @enderror" required>
                        <option value="all" {{ old('target') == 'all' ? 'selected' : '' }}>{{ __('announcements.target_all') }}</option>
                        <option value="local" {{ old('target') == 'local' ? 'selected' : '' }}>{{ __('announcements.target_local_only') }}</option>
                        <option value="foreign" {{ old('target') == 'foreign' ? 'selected' : '' }}>{{ __('announcements.target_foreign_only') }}</option>
                    </select>
                    @error('target')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('announcements.start_date') }}</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date') }}">
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">{{ __('announcements.leave_empty_immediate') }}</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('announcements.end_date') }}</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date') }}">
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">{{ __('announcements.leave_empty_no_limit') }}</small>
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="send_email" value="1"
                           id="sendEmail" {{ old('send_email') ? 'checked' : '' }}>
                    <label class="form-check-label" for="sendEmail">
                        {{ __('announcements.send_email_label') }}
                    </label>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('announcements.queue_info') }}
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-paper-plane me-1"></i>{{ __('announcements.publish') }}
                </button>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">{{ __('general.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .type-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 16px 32px;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
        user-select: none;
    }
    .type-card:hover { border-color: #1a5f4a; color: #1a5f4a; }
    .type-card.active {
        border-color: #1a5f4a;
        background: #f0fdf4;
        color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26,95,74,0.15);
    }

    .form-builder-wrapper {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .fb-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e2e8f0;
    }
    .fb-fields {
        padding: 16px;
        min-height: 80px;
    }
    .fb-empty {
        text-align: center;
        padding: 30px;
        color: #94a3b8;
    }
    .fb-empty i {
        font-size: 40px;
        margin-bottom: 12px;
        display: block;
        opacity: 0.5;
    }
    .fb-empty p { margin: 0; font-size: 14px; }

    .fb-add-bar {
        border-top: 2px dashed #e2e8f0;
        padding: 14px 16px;
        background: #fafbfc;
    }
    .fb-add-types {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }
    .fb-type-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        cursor: pointer;
        transition: all 0.15s;
        color: #475569;
        font-size: 11px;
        min-width: 75px;
    }
    .fb-type-btn i { font-size: 16px; color: #1a5f4a; }
    .fb-type-btn:hover {
        border-color: #1a5f4a;
        background: #f0fdf4;
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.08);
    }

    .field-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-bottom: 10px;
        background: #fff;
        transition: all 0.2s;
        overflow: hidden;
        animation: slideIn 0.25s ease;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .field-card:hover {
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }
    .field-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }
    .field-card-header .field-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #fff;
        background: #1a5f4a;
        flex-shrink: 0;
    }
    .field-card-header .field-num {
        font-weight: 700;
        color: #1a5f4a;
        font-size: 13px;
        min-width: 20px;
    }
    .field-card-header .field-type-name {
        font-size: 12px;
        color: #94a3b8;
        flex: 1;
    }
    .field-card-header .btn-field-remove {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s;
        font-size: 12px;
    }
    .field-card-header .btn-field-remove:hover {
        background: #ef4444;
        color: #fff;
    }
    .field-card-body {
        padding: 14px;
    }
    .field-card-body .form-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .options-wrapper {
        display: none;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }
    .options-wrapper.show { display: block; }
</style>
@endpush

@push('scripts')
<script>
var fieldCounter = 0;
var typeIcons = {
    text: 'fa-font', textarea: 'fa-align-left', number: 'fa-hashtag',
    email: 'fa-at', date: 'fa-calendar-alt', select: 'fa-caret-square-down',
    radio: 'fa-dot-circle', checkbox: 'fa-check-square', file: 'fa-paperclip'
};
var typeNames = @json(__('announcements.field_types'));

function toggleFormBuilder() {
    var isForm = document.getElementById('typeForm').checked;
    document.getElementById('formBuilderSection').style.display = isForm ? 'block' : 'none';
    if (isForm) {
        document.getElementById('bodyLabel').innerHTML = '{{ __("announcements.form_description") }} <span class="text-danger">*</span>';
    } else {
        document.getElementById('bodyLabel').innerHTML = '{{ __("announcements.announcement_body") }} <span class="text-danger">*</span>';
    }
}

function updateFieldCount() {
    var count = document.querySelectorAll('#fieldsContainer .field-card').length;
    document.getElementById('fieldCountText').textContent = count + ' {{ __("announcements.field_type") }}';
    document.getElementById('addFieldBar').style.display = count > 0 ? 'block' : 'none';
    var noMsg = document.getElementById('noFieldsMsg');
    if (noMsg) noMsg.style.display = count > 0 ? 'none' : 'block';
}

function addFieldOfType(type) {
    fieldCounter++;
    var fieldName = 'field_' + fieldCounter;
    var icon = typeIcons[type] || 'fa-font';
    var typeName = typeNames[type] || type;
    var hasOptions = ['select', 'radio', 'checkbox'].indexOf(type) !== -1;
    var noPlaceholder = ['file', 'checkbox'].indexOf(type) !== -1;

    var html = '<div class="field-card" data-field="' + fieldName + '" data-type="' + type + '" id="fieldCard_' + fieldName + '">' +
        '<div class="field-card-header">' +
            '<span class="field-icon"><i class="fas ' + icon + '"></i></span>' +
            '<span class="field-num"></span>' +
            '<span class="field-type-name">' + typeName + '</span>' +
            '<button type="button" class="btn-field-remove" onclick="removeField(\'' + fieldName + '\')" title="{{ __("announcements.remove_field") }}">' +
                '<i class="fas fa-times"></i>' +
            '</button>' +
        '</div>' +
        '<div class="field-card-body">' +
            '<div class="row g-3">' +
                '<div class="' + (noPlaceholder ? 'col-md-10' : 'col-md-5') + '">' +
                    '<label class="form-label">{{ __("announcements.field_label") }} *</label>' +
                    '<input type="text" class="form-control form-control-sm field-label-input" placeholder="{{ __("announcements.field_label_placeholder") }}">' +
                '</div>' +
                (noPlaceholder ? '' :
                '<div class="col-md-5">' +
                    '<label class="form-label">{{ __("announcements.field_placeholder") }}</label>' +
                    '<input type="text" class="form-control form-control-sm field-placeholder-input" placeholder="{{ __("announcements.field_placeholder") }}">' +
                '</div>') +
                '<div class="col-md-2 d-flex align-items-end pb-1">' +
                    '<div class="form-check form-switch">' +
                        '<input class="form-check-input field-required-check" type="checkbox" checked id="req_' + fieldName + '">' +
                        '<label class="form-check-label small" for="req_' + fieldName + '">{{ __("announcements.field_required") }}</label>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            (hasOptions ?
            '<div class="options-wrapper show">' +
                '<label class="form-label">{{ __("announcements.field_options") }} *</label>' +
                '<textarea class="form-control form-control-sm field-options-textarea" rows="3" placeholder="{{ str_replace(["\r\n", "\n", "\r"], "&#10;", __("announcements.options_hint")) }}"></textarea>' +
            '</div>' : '') +
        '</div>' +
    '</div>';

    var noMsg = document.getElementById('noFieldsMsg');
    if (noMsg) noMsg.style.display = 'none';
    document.getElementById('fieldsContainer').insertAdjacentHTML('beforeend', html);
    renumberFields();
    updateFieldCount();

    var newCard = document.getElementById('fieldCard_' + fieldName);
    var labelInput = newCard.querySelector('.field-label-input');
    if (labelInput) labelInput.focus();
}

function removeField(fieldName) {
    var card = document.getElementById('fieldCard_' + fieldName);
    if (card) {
        card.style.animation = 'slideIn 0.2s ease reverse';
        setTimeout(function() {
            card.remove();
            renumberFields();
            updateFieldCount();
        }, 180);
    }
}

function renumberFields() {
    var cards = document.querySelectorAll('#fieldsContainer .field-card');
    cards.forEach(function(card, i) {
        var num = card.querySelector('.field-num');
        if (num) num.textContent = '#' + (i + 1);
    });
}

function collectFields() {
    var fields = [];
    var cards = document.querySelectorAll('#fieldsContainer .field-card');
    var counter = 0;

    cards.forEach(function(card) {
        counter++;
        var type = card.getAttribute('data-type');
        var label = card.querySelector('.field-label-input').value.trim();
        var placeholderEl = card.querySelector('.field-placeholder-input');
        var placeholder = placeholderEl ? placeholderEl.value.trim() : '';
        var required = card.querySelector('.field-required-check').checked;
        var optionsTextarea = card.querySelector('.field-options-textarea');
        var options = null;

        if (['select', 'radio', 'checkbox'].indexOf(type) !== -1 && optionsTextarea) {
            var raw = optionsTextarea.value.trim();
            if (raw) {
                options = raw.split('\n').map(function(o) { return o.trim(); }).filter(function(o) { return o; });
            }
        }

        if (label) {
            fields.push({
                name: 'field_' + counter,
                type: type,
                label: label,
                placeholder: placeholder || null,
                required: required,
                options: options,
                order: counter
            });
        }
    });

    return fields;
}

document.querySelectorAll('.type-card').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.type-card').forEach(function(c) { c.classList.remove('active'); });
        this.classList.add('active');
        var radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
        toggleFormBuilder();
    });
});

document.getElementById('addFieldBtn').addEventListener('click', function() {
    addFieldOfType('text');
});

document.querySelectorAll('.fb-type-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        addFieldOfType(this.getAttribute('data-type'));
    });
});

toggleFormBuilder();
updateFieldCount();

document.getElementById('announcementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var isForm = document.getElementById('typeForm').checked;

    if (isForm) {
        var fields = collectFields();
        if (fields.length === 0) {
            Swal.fire({ title: '{{ __("general.error") }}', text: '{{ __("announcements.form_fields_required") }}', icon: 'error', confirmButtonColor: '#dc2626' });
            return;
        }

        var hasEmptyLabel = false;
        var hasOptionsError = false;
        var cards = document.querySelectorAll('#fieldsContainer .field-card');
        cards.forEach(function(card) {
            if (!card.querySelector('.field-label-input').value.trim()) hasEmptyLabel = true;
            var type = card.getAttribute('data-type');
            if (['select', 'radio', 'checkbox'].indexOf(type) !== -1) {
                var opts = card.querySelector('.field-options-textarea');
                if (opts && !opts.value.trim()) hasOptionsError = true;
            }
        });
        if (hasEmptyLabel) {
            Swal.fire({ title: '{{ __("general.error") }}', text: '{{ __("announcements.field_label") }}', icon: 'error', confirmButtonColor: '#dc2626' });
            return;
        }
        if (hasOptionsError) {
            Swal.fire({ title: '{{ __("general.error") }}', text: '{{ __("announcements.field_options") }}', icon: 'error', confirmButtonColor: '#dc2626' });
            return;
        }

        document.getElementById('formFieldsInput').value = JSON.stringify(fields);
    }

    var sendEmail = document.getElementById('sendEmail').checked;
    var message = '{{ __("announcements.publish_confirm") }}';
    if (sendEmail) {
        message += ' {{ __("announcements.publish_with_email") }}';
    }

    Swal.fire({
        title: '{{ __("announcements.confirm_publish") }}',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1a5f4a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '{{ __("announcements.yes_publish") }}',
        cancelButtonText: '{{ __("general.cancel") }}'
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("announcements.publishing") }}';
            form.submit();
        }
    });
});
</script>
@endpush
