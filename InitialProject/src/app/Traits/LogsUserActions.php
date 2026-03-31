<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsUserActions
{
    /**
     * Log a user action with detailed information
     *
     * @param string $action The action being performed (e.g., 'update', 'upload', 'delete')
     * @param string $entityType The type of entity being acted upon (e.g., 'profile', 'paper', 'research')
     * @param mixed $entityId The ID of the entity (optional)
     * @param array $details Additional details about the action (optional)
     * @return void
     */
    protected function logUserAction($action, $entityType, $entityId = null, $details = [])
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        $actionType = ucfirst($action);
        $actionText = $actionType . ' ' . $entityType;
        
        $description = "User performed {$action} on {$entityType}";
        
        if ($entityId) {
            $description .= " with ID: {$entityId}";
        }
        
        if (!empty($details)) {
            // Filter sensitive information
            $filteredDetails = collect($details)->except(['password', 'password_confirmation'])->toArray();
            if (!empty($filteredDetails)) {
                $detailsJson = json_encode($filteredDetails, JSON_UNESCAPED_UNICODE);
                $description .= " with details: {$detailsJson}";
            }
        }
        
        ActivityLog::log($user->id, $actionText, $description);
    }
    
    /**
     * Log a create action
     */
    protected function logCreate($entityType, $entityId = null, $details = [])
    {
        $this->logUserAction('create', $entityType, $entityId, $details);
    }
    
    /**
     * Log an update action
     */
    protected function logUpdate($entityType, $entityId = null, $details = [])
    {
        $this->logUserAction('update', $entityType, $entityId, $details);
    }
    
    /**
     * Log an upload action
     */
    protected function logUpload($entityType, $entityId = null, $details = [])
    {
        $this->logUserAction('upload', $entityType, $entityId, $details);
    }
    
    /**
     * Log a delete action
     */
    protected function logDelete($entityType, $entityId = null, $details = [])
    {
        $this->logUserAction('delete', $entityType, $entityId, $details);
    }
} 