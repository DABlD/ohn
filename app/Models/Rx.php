<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use App\Traits\RxAttribute;
use App\Models\Rx;

class Rx extends Model
{
    use RxAttribute;

    protected $fillable = [
        'ticket_number','patient_id','patient_name',
        'contact','address','amount','date', 'doctor_id'
    ];

    protected $dates = [
        'created_at', 'updated_at', 'deleted_at', 'date'
    ];
}
