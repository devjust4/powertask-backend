<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'student_id',
    ];


    public function tasks() {
        return $this->hasMany(Task::class);
    }
    public function events() {
        return $this->hasMany(Event::class);
    }
    public function periods() {
        return $this->hasMany(Period::class);
    }
    public function blocks() {
        return $this->hasMany(Block::class);
    }
    public function courses() {
        return $this->hasMany(Course::class);
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
}
