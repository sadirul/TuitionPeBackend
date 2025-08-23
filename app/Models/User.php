<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $appends = ['is_expired'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    protected $fillable = [
        'name',
        'email',
        'password',
        'tuition_id',
        'tuition_name',
        'username',
        'mobile',
        'address',
        'role',
        'expiry_datetime',
        'upi_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tuition()
    {
        return $this->belongsTo(User::class, 'tuition_id');
    }


    public function studentsEnrolled()
    {
        // For teachers/tuitions
        return $this->hasMany(Student::class, 'tuition_id');
    }

    public function clsses()
    {
        return $this->hasMany(Classes::class, 'tuition_id');
    }

    public function studentInfo()
    {
        // For students
        return $this->hasOne(Student::class, 'user_id');
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'tuition_id');
    }

    // App\Models\User.php
    public function fees()
    {
        return $this->hasManyThrough(
            Fee::class,           // Final model
            Student::class,   // Intermediate model
            'user_id',            // FK on student_infos table
            'student_id',         // FK on fees table
            'id',                 // Local key on users table
            'id'                  // Local key on student_infos table
        );
    }

    protected function isExpired(): Attribute
    {
        return Attribute::get(function () {
            return $this->expiry_datetime
                ? now()->greaterThan($this->expiry_datetime)
                : false;
        });
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
