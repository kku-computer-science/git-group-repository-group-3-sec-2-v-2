<?php

namespace App\Http\Controllers;
use App\Models\ResearchGroup;
use Illuminate\Http\Request;

class ResearchgroupsController extends Controller
{
    public function index(Request $request)
    {
        // รับคำค้นหา
        $search = $request->input('textsearch');
        
        // ตรวจสอบภาษาปัจจุบัน
        $locale = app()->getLocale(); // 'th' หรือ 'en'
        
        // ดึงข้อมูล Research Groups พร้อมค้นหา
        $query = ResearchGroup::with('user');
        
        // ถ้ามีการค้นหา
        if ($search) {
            $query->where(function($q) use ($search, $locale) {
                $q->where('group_name_'.$locale, 'LIKE', "%{$search}%")
                  ->orWhere('group_name_en', 'LIKE', "%{$search}%") // ค้นหาในชื่อภาษาอังกฤษเสมอ
                  ->orWhere('group_desc_'.$locale, 'LIKE', "%{$search}%")
                  ->orWhere('group_detail_'.$locale, 'LIKE', "%{$search}%")
                  ->orWhere('group_main_research_'.$locale, 'LIKE', "%{$search}%");
            });
        }
        
        // เรียงตามชื่อกลุ่มตามภาษาที่ใช้งาน
        $resg = $query->orderBy('group_name_'.$locale)->get();
        
        // ตรวจสอบว่ามีการค้นหาและไม่พบผลลัพธ์
        $noResults = !empty($search) && $resg->isEmpty();
        
        return view('research_g', compact('resg', 'search', 'noResults'));
    }
}
