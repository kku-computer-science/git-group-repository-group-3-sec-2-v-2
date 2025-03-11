<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class UsersExport implements FromArray
{
    protected $tags;

    public function __construct($tags)
    {
        $this->tags = $tags;
    }

    public function array(): array
    {
        // เพิ่ม Headers เป็นแถวแรก
        $headers = [
            'Author',              // ชื่อผู้เขียน
            'Paper Name',          // ชื่อเอกสาร
            'Year Published',      // ปีที่ตีพิมพ์
            'Source Title',        // ชื่อแหล่งที่มา
            'Volume',              // ฉบับที่
            'Issue',               // หมายเลขฉบับ
            'Page Start',          // หน้าเริ่มต้น
            'Page End',            // หน้าสิ้นสุด
            'Citations',           // การอ้างอิง
            'DOI',                 // DOI
            'Subtype',             // ประเภทย่อย
        ];

        // รวม Headers กับข้อมูลใน `$tags`
        return array_merge([$headers], $this->tags);
    }
}
