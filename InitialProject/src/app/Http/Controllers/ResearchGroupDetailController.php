<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Paper;
use App\Models\ResearchGroup;
use Illuminate\Http\Request;

class ResearchGroupDetailController extends Controller
{
    public function request($id)
    {
<<<<<<< HEAD
        $resgd = ResearchGroup::with(['User.paper' => function ($query) {
            return $query->orderBy('paper_yearpub','DESC');
        }])->where('id','=',$id)->get();
=======
        // ดึงข้อมูลกลุ่มวิจัยพร้อมความสัมพันธ์ที่ต้องการ
        $researchGroup = ResearchGroup::with(['User.paper' => function ($query) {
            return $query->orderBy('paper_yearpub', 'DESC');
        }])->findOrFail($id);
>>>>>>> 2773d82 (feat(ResearchGroup): Add link column to research_groups table and update controller logic for link handling)

        // ตรวจสอบว่ามีค่า link หรือไม่ ถ้ามีให้ re‑direct ไปยัง URL นั้น
        if (!empty($researchGroup->link)) {
            return redirect()->away($researchGroup->link);
        }

        // หากไม่มี link ส่งข้อมูลออกไปยัง view โดยห่อด้วย collection เพื่อให้เข้ากับการวนลูปใน view เดิม
        return view('researchgroupdetail', ['resgd' => collect([$researchGroup])]);
    }
<<<<<<< HEAD
=======

    // ฟังก์ชัน user() ด้านล่างนี้ดูเหมือนจะไม่ใช่ส่วนที่เกี่ยวข้องกับ Controller
    // ควรอยู่ใน Model แต่หากยังคงใช้งานก็สามารถไว้ได้
    public function user()
    {
        return $this->belongsToMany(User::class, 'work_of_research_groups')
            ->withPivot('role');
    }
>>>>>>> 2773d82 (feat(ResearchGroup): Add link column to research_groups table and update controller logic for link handling)
}
