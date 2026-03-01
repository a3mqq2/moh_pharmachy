<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CompanyRepresentative extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'job_title',
        'phone',
        'email',
        'password',
        'is_verified',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function companies()
    {
        return $this->hasMany(LocalCompany::class, 'representative_id');
    }

    public function foreignCompanies()
    {
        return $this->hasMany(ForeignCompany::class, 'representative_id');
    }
}
