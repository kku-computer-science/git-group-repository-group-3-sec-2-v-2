<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Paper;
use App\Models\ResearchGroup;
use App\Models\User;
use Illuminate\Http\Request;

class ResearchGroupDetailController extends Controller
{

    public function request($id)
    {
        $researchGroup = ResearchGroup::with([
            'user.paper' => function ($query) {
                return $query->orderBy('paper_yearpub', 'DESC');
            },
            'visitingScholars'
        ])->findOrFail($id);

        if (!empty($researchGroup->link)) {
            return redirect()->away($researchGroup->link);
        }
        $researchGroup->user = $researchGroup->user->map(function ($user) {
            $user->role = $user->pivot->role;
            $user->can_edit = $user->pivot->can_edit;
            $user->author_id = $user->pivot->author_id;
            return $user;
        });

        $researchGroup->visitingScholars = $researchGroup->visitingScholars->map(function ($author) {
            $author->author_id = $author->pivot->author_id;
            return $author;
        });

        // dd($researchGroup);

        return view('researchgroupdetail', ['resgd' => collect([$researchGroup])]);
    }


    // ฟังก์ชัน user() ด้านล่างนี้ดูเหมือนจะไม่ใช่ส่วนที่เกี่ยวข้องกับ Controller
    // ควรอยู่ใน Model แต่หากยังคงใช้งานก็สามารถไว้ได้
    public function user()
    {
        return $this->belongsToMany(User::class, 'work_of_research_groups')
            ->withPivot('role');
    }
}
