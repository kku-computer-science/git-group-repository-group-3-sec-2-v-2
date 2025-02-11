<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPaper extends Model
{
    use HasFactory;

    protected $table = 'user_papers'; // กำหนดชื่อตาราง
    protected $primaryKey = 'id';
    public $timestamps = false; // ปิด timestamps ถ้าตารางไม่มี created_at และ updated_at

    protected $fillable = ['user_id', 'paper_id', 'author_type'];

    // ความสัมพันธ์กับ Users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ความสัมพันธ์กับ Papers
    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
}
