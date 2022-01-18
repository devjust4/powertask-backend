<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    public function subject() {
        return $this->belongsTo(Subject::class);
    }
    public function period() {
        return $this->belongsTo(Period::class);
    }
}
