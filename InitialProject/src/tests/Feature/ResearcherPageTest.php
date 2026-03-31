<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Program;
use Spatie\Permission\Models\Role;

class ResearcherPageTest extends TestCase
{
    /**
     * Test that the researcher page loads successfully.
     *
     * @return void
     */
    public function test_researcher_page_loads_successfully()
    {
        // ตรวจสอบว่าหน้าเว็บโหลดสำเร็จ
        $response = $this->get(route('researchers.index'));
        $response->assertStatus(200);
        
        // ตรวจสอบว่ามีคำว่า "Researchers" หรือ "OUR RESEARCHERS" บนหน้าเว็บ
        $response->assertSee('Researchers', false);
    }
}