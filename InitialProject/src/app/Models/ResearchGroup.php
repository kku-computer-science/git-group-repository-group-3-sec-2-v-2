<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchGroup extends Model
{
    use HasFactory;

    protected $fillable = [
<<<<<<< HEAD
        'group_url', 'group_name_th', 'group_name_en', 
        'group_detail_th', 'group_detail_en', 
        'group_desc_th', 'group_desc_en', 
        'group_image',
=======
        'group_name_th',
        'group_name_en',
        'group_detail_th',
        'group_detail_en',
        'group_desc_th',
        'group_desc_en',
        'group_image',
        'link',
        'group_main_research_en',
        'group_main_research_th'
>>>>>>> origin/main
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'work_of_research_groups')
            ->withPivot('role', 'can_edit', 'user_id');
    }
    public function product()
    {
        return $this->hasOne(Product::class, 'group_id');
    }

    public function visitingScholars()
    {
        return $this->belongsToMany(Author::class, 'work_of_research_groups', 'research_group_id', 'author_id')
                    ->withPivot('role', 'can_edit', 'author_id')
                    ->wherePivot('role', 4);
    }    
}
