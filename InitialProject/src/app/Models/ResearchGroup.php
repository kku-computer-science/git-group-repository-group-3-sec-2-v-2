<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_name_th', 'group_name_en', 'group_detail_th', 'group_detail_en', 'group_desc_th', 'group_desc_en', 'group_image', 'link', 'group_main_research_en', 'group_main_research_th'
    ];

    public function user()
    {
        return $this->belongsToMany(User::class,'work_of_research_groups')->withPivot('role', 'can_edit');
        // OR return $this->hasOne('App\Phone');
    }
    public function product(){
        return $this->hasOne(Product::class,'group_id');
    }
}
