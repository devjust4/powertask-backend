<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    public function student() {
        return $this->belongsTo(Student::class);
    }
    public function task() {
        return $this->belongsTo(Task::class);
    }
}
