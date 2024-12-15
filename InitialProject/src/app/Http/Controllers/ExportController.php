<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ExportController extends Controller
{
    function __construct()
    {
        // กำหนด middleware เพื่อให้เฉพาะผู้ที่มีสิทธิ์ 'export' เท่านั้นที่สามารถเข้าถึงฟังก์ชัน 'index' ได้
        $this->middleware('permission:export')->only('index');
        // Redirect::to('dashboard')->send();
    }

    public function index()
    {
        // ดึงข้อมูลผู้ใช้ที่มีบทบาทเป็น 'teacher'
        $data = User::role('teacher')->get();
        //return $data;
        // ส่งข้อมูลไปยัง view 'export.index'
        return view('export.index', compact('data'));
    }
}
