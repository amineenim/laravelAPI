<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edt extends Model
{
    use HasFactory;

    protected $table = 'edt';
    protected $primaryKey = 'id_edt';
}
