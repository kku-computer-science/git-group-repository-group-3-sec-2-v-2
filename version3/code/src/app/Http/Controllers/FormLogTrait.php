<?php

namespace App\Http\Controllers;

use App\Services\FormSubmitService;

trait FormLogTrait
{
    /**
     * Log a form submission
     *
     * @param string $action The action being performed (e.g., 'submit', 'update', 'delete')
     * @param string $formName The name of the form
     * @param array $formData The form data
     * @return void
     */
    protected function logFormSubmission($action, $formName, $formData)
    {
        // Format the full form name with action
        $fullFormName = ucfirst($action) . ' ' . $formName;
        
        // Log the form submission
        FormSubmitService::logSubmission($fullFormName, $formData);
    }
} 