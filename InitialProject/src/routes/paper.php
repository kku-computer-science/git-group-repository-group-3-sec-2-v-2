<?php

// ตั้งค่า path ของโฟลเดอร์ input และ output
$inputPath = "D:/sprint1/git-group-repository-group-3-sec-2-v-2/InitialProject/src/app/Public/Scholar";
$outputPath = "D:/sprint1/git-group-repository-group-3-sec-2-v-2/InitialProject/src/storage/Paper/output.json";

// ค้นหาไฟล์ JSON ทั้งหมดในโฟลเดอร์
$jsonFiles = glob($inputPath . "/*.json");

// ตรวจสอบว่าเจอไฟล์ JSON หรือไม่
if (!$jsonFiles) {
    die("❌ ไม่พบไฟล์ JSON ในโฟลเดอร์: $inputPath\n");
}

echo "✅ พบไฟล์ JSON จำนวน: " . count($jsonFiles) . "\n";

// ตัวแปรเก็บข้อมูลนักวิจัย
$researchers = [];

foreach ($jsonFiles as $file) {
    echo "📂 กำลังอ่านไฟล์: $file\n";

    // อ่านเนื้อหา JSON
    $jsonContent = file_get_contents($file);
    if (!$jsonContent) {
        echo "⚠️ ไม่สามารถอ่านไฟล์: $file\n";
        continue;
    }

    // แปลง JSON เป็น array
    $data = json_decode($jsonContent, true);

    // ตรวจสอบว่ามีข้อมูลที่ต้องการหรือไม่
    if (!isset($data['name'], $data['publications'])) {
        echo "⚠️ ไฟล์ $file ไม่มีข้อมูลที่ต้องการ\n";
        continue;
    }

    // ดึงข้อมูลที่ต้องการ
    $name = $data['name'] ?? 'ไม่ทราบชื่อ';
    $affiliation = $data['affiliation'] ?? 'ไม่ระบุตำแหน่ง';
    $publications = $data['publications'] ?? [];

    // แปลงข้อมูลผลงานวิจัย
    $papers = [];
    foreach ($publications as $publication) {
        $papers[] = [
            "title" => $publication['title'] ?? 'ไม่ทราบชื่อเรื่อง',
            "year" => $publication['year'] ?? 'ไม่ระบุปี',
            "link" => $publication['link'] ?? '',
            "authors" => $publication['authors'] ?? 'ไม่ระบุผู้แต่ง'
        ];
    }

    // บันทึกข้อมูลของนักวิจัย
    $researchers[] = [
        "name" => $name,
        "affiliation" => $affiliation,
        "publications" => $papers
    ];
}

// ตรวจสอบว่าได้ข้อมูลหรือไม่
if (empty($researchers)) {
    die("❌ ไม่มีข้อมูลนักวิจัยที่มีผลงานวิจัย\n");
}

// บันทึกผลลัพธ์ลงไฟล์ JSON
$result = file_put_contents($outputPath, json_encode($researchers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result) {
    echo "✅ บันทึกข้อมูลสำเร็จที่: $outputPath\n";
} else {
    echo "❌ ไม่สามารถบันทึกข้อมูลได้\n";
}

?>
