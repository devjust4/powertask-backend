<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'student_id',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function subject() {
        return $this->belongsTo(Subject::class);
    }
}
