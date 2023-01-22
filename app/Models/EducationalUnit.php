<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalUnit extends Model
{
    use HasFactory;

    protected $table = 'ue';
    protected $primaryKey = 'id_ue';
}
