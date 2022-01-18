<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    public function tasks() {
        return $this->hasMany(Task::class);
    }
    public function events() {
        return $this->hasMany(Event::class);
    }
    public function blocks() {
        return $this->hasMany(Block::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }
}
