<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Program;

class ResearcherPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_researcher_page_loads_successfully()
    {
        $response = $this->get(route('researchers.index'));

        // Assert the page loads successfully
        $response->assertStatus(200);
        $response->assertSee('Researchers'); // Ensure the page contains the word 'Researchers'
    }
}