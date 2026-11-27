<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LaunchAttendee extends Model {
    protected $fillable = ['name','phone','email','gender','church','college','downloaded','sms_status','email_status'];
    protected $casts = ['downloaded'=>'boolean'];
}
