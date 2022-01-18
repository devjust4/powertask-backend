<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    public function subjects() {
        return $this->hasMany(Subject::class);
    }
    public function blocks() {
        return $this->hasMany(Block::class);
    }

    public function student() {
        return $this->belongsTo(Student::class);
    }
}
