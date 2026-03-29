<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class LocalCompanyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_company_id',
        'document_type',
        'custom_name',
        'original_name',
        'file_path',
        'file_extension',
        'file_size',
        'notes',
        'uploaded_by',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    public function localCompany()
    {
        return $this->belongsTo(LocalCompany::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function documentTypes()
    {
        return [
            'establishment_contract' => __('documents.local_type_establishment_contract'),
            'control_certificate' => __('documents.local_type_control_certificate'),
            'commercial_license' => __('documents.local_type_commercial_license'),
            'chamber_certificate' => __('documents.local_type_chamber_certificate'),
            'commercial_register' => __('documents.local_type_commercial_register'),
            'assignment_letter' => __('documents.local_type_assignment_letter'),
            'practice_permit' => __('documents.local_type_practice_permit'),
            'official_email' => __('documents.local_type_official_email'),
            'factory_registration' => __('documents.local_type_factory_registration'),
            'other' => __('documents.type_other'),
        ];
    }

    public function getDocumentTypeNameAttribute()
    {
        return self::documentTypes()[$this->document_type] ?? $this->document_type;
    }

    public function getDisplayNameAttribute()
    {
        if ($this->document_type == 'other' && $this->custom_name) {
            return $this->custom_name;
        }
        return $this->document_type_name;
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getFileIconAttribute()
    {
        return match(strtolower($this->file_extension)) {
            'pdf' => 'ti-file-type-pdf text-danger',
            'doc', 'docx' => 'ti-file-type-doc text-primary',
            'xls', 'xlsx' => 'ti-file-spreadsheet text-success',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'ti-photo text-info',
            'zip', 'rar' => 'ti-file-zip text-warning',
            default => 'ti-file text-secondary',
        };
    }

    public function getFileUrl()
    {
        return Storage::url($this->file_path);
    }

    public function isImage()
    {
        return in_array(strtolower($this->file_extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    public function updateRequests()
    {
        return $this->morphMany(DocumentUpdateRequest::class, 'documentable');
    }

    public function pendingUpdateRequest()
    {
        return $this->morphOne(DocumentUpdateRequest::class, 'documentable')->where('status', 'pending');
    }

    public static function requiredDocumentTypes()
    {
        return [
            'establishment_contract',
            'control_certificate',
            'commercial_license',
            'chamber_certificate',
            'commercial_register',
            'assignment_letter',
            'practice_permit',
            'official_email',
            'factory_registration',
        ];
    }
}
