<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdminDocument extends Model
{
    protected $fillable = [
        'title',
        'category',
        'file_path',
        'original_name',
        'file_extension',
        'file_size',
        'notes',
        'uploaded_by',
        'visibility',
        'department_id',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function authorizedUsers()
    {
        return $this->belongsToMany(User::class, 'admin_document_users')->withTimestamps();
    }

    public function isVisibleTo(User $user): bool
    {
        if ($this->visibility === 'all') {
            return true;
        }

        if ($this->visibility === 'department' && $this->department_id) {
            if ($user->department_id == $this->department_id) {
                return true;
            }
            $department = Department::find($this->department_id);
            if ($department && $department->parent_id && $user->department_id == $department->parent_id) {
                return true;
            }
            if ($department) {
                $childIds = Department::where('parent_id', $this->department_id)->pluck('id')->toArray();
                if (in_array($user->department_id, $childIds)) {
                    return true;
                }
            }
            return false;
        }

        if ($this->visibility === 'specific') {
            return $this->authorizedUsers()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public static function visibilityOptions(): array
    {
        return [
            'all' => 'الجميع',
            'department' => 'قسم محدد',
            'specific' => 'مستخدمين محددين',
        ];
    }

    public static function categories(): array
    {
        return [
            'circulars' => 'تعاميم',
            'decisions' => 'قرارات',
            'meeting_minutes' => 'محاضر اجتماعات',
            'templates' => 'نماذج عمل',
            'policies' => 'سياسات وإجراءات',
            'reports' => 'تقارير',
            'contracts' => 'عقود واتفاقيات',
            'letters' => 'خطابات رسمية',
            'manuals' => 'أدلة إرشادية',
            'plans' => 'خطط عمل',
            'forms' => 'نماذج رسمية',
            'regulations' => 'لوائح وأنظمة',
            'training' => 'مواد تدريبية',
            'financial' => 'مستندات مالية',
            'hr' => 'موارد بشرية',
            'technical' => 'مستندات تقنية',
            'other' => 'أخرى',
        ];
    }

    public function getCategoryNameAttribute(): string
    {
        return self::categories()[$this->category] ?? $this->category;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        return number_format($bytes / 1024, 1) . ' KB';
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
