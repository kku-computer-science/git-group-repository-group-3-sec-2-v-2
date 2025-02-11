<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorOfPaper extends Model
{
    use HasFactory;

    protected $table = 'author_of_papers';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['author_id', 'paper_id', 'author_type'];

    // ความสัมพันธ์กับ Authors
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    // ความสัมพันธ์กับ Papers
    public function paper()
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }
}
