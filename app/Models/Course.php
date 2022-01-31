<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    public function subjects() {
        return $this->hasMany(Subject::class);
    }
    public function students() {
        return $this->hasMany(Student::class);
    }

    // public function student() {
    //     return $this->belongsTo(Student::class);
    // }
}
