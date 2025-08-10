<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role');
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function insurers()
    {
        return $this->hasMany(Insurer::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}
