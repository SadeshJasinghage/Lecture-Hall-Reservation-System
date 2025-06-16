<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityHistory extends Model
{
    use HasFactory;

    protected $table = 'activity_history';
    protected $fillable = [
        'reservation_id', 'user', 'role', 'requested_date',
        'hall_name', 'course_code', 'date', 'start_time', 'end_time',
        'status', 'approval_status'
    ];
}
