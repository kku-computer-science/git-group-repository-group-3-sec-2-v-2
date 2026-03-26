<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Paper;
use App\Models\ResearchGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResearchGroupDetailController extends Controller
{

    public function request($id)
    {
        $researchGroup = ResearchGroup::with([
            'user.roles',
            'visitingScholars'
        ])->findOrFail($id);

        if (!empty($researchGroup->link)) {
            return redirect()->away($researchGroup->link);
        }

        $users = $researchGroup->user;

        $headLabs = $users->filter(function ($user) {
            return $user->hasRole('teacher') && (int) optional($user->pivot)->role === 1;
        })->values();

        $members = $users->filter(function ($user) {
            return $user->hasRole('teacher') && (int) optional($user->pivot)->role === 2;
        })->values();

        $postdocInternal = $users->filter(function ($user) {
            return (int) optional($user->pivot)->role === 3;
        })->values();

        $students = $users->filter(function ($user) {
            return $user->hasRole('student') && (int) optional($user->pivot)->role === 2;
        })->unique('id')->values();

        $postdocExternalIds = DB::table('work_of_research_groups')
            ->where('research_group_id', $researchGroup->id)
            ->where('role', 3)
            ->whereNull('user_id')
            ->whereNotNull('author_id')
            ->distinct()
            ->pluck('author_id');

        $postdocExternal = $postdocExternalIds->isNotEmpty()
            ? Author::whereIn('id', $postdocExternalIds)->get()->values()
            : collect();

        $visitingScholars = $researchGroup->visitingScholars->values();

        return view('researchgroupdetail', compact(
            'researchGroup',
            'headLabs',
            'members',
            'postdocInternal',
            'postdocExternal',
            'students',
            'visitingScholars'
        ));
    }


    // ฟังก์ชัน user() ด้านล่างนี้ดูเหมือนจะไม่ใช่ส่วนที่เกี่ยวข้องกับ Controller
    // ควรอยู่ใน Model แต่หากยังคงใช้งานก็สามารถไว้ได้
    public function user()
    {
        return $this->belongsToMany(User::class, 'work_of_research_groups')
            ->withPivot('role');
    }
}
