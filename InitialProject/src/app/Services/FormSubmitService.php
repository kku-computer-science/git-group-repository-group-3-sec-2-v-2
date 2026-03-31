<?php

namespace App\Services;

use Illuminate\Support\Facades\Event;

class FormSubmitService
{
    /**
     * Log a form submission
     *
     * @param string $formName The name of the form
     * @param array $formData The form data
     * @param int|null $userId The ID of the user who submitted the form (optional)
     * @return void
     */
    public static function logSubmission($formName, $formData, $userId = null)
    {
        // Emit the form.submitted event
        Event::dispatch('form.submitted', [$formName, $formData, $userId]);
    }
} 