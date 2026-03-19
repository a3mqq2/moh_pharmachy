<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id')->orderBy('sort_order');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function isMainDepartment(): bool
    {
        return is_null($this->parent_id);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' - ' . $this->name;
        }
        return $this->name;
    }

    public function getAllUsersCountAttribute(): int
    {
        $count = $this->users()->count();
        foreach ($this->children as $child) {
            $count += $child->users()->count();
        }
        return $count;
    }
}
