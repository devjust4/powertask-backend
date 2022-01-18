<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public function tasks() {
        return $this->hasMany(Task::class);
    }
    public function events() {
        return $this->hasMany(Event::class);
    }
    public function periods() {
        return $this->hasMany(Period::class);
    }
    public function sessions() {
        return $this->hasMany(Session::class);
    }
    public function courses() {
        return $this->hasManyThrough(Course::class, Enrollment::class);
    }

    public function enrollments() {
        return $this->belongsTo(Enrollment::class);
    }
}
