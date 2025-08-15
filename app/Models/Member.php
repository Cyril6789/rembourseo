<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'first_name',
        'last_name',
        'email',
        'birthdate',
        'role', // 'parent' ou 'child'
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }

    public function scopeParents($q) { return $q->where('role', 'parent'); }
    public function scopeChildren($q) { return $q->where('role', 'child'); }
}
