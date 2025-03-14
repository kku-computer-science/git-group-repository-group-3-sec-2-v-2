<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ErrorLogService
{
    /**
     * Log system exception
     * 
     * @param \Throwable $exception The exception to log
     * @param string $location Optional location information (e.g., 'Controller@method')
     * @return void
     */
    public static function logException(\Throwable $exception, $location = null)
    {
        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'input' => self::filterSensitiveData(Request::all()),
            'user_agent' => Request::userAgent(),
            'ip' => Request::ip(),
            'user_id' => Auth::id(),
            'location' => $location
        ];

        ErrorLog::create([
            'level' => 'error',
            'message' => $exception->getMessage(),
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'ip_address' => Request::ip(),
            'user_id' => Auth::id(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'user_agent' => Request::userAgent()
        ]);
    }

    /**
     * Log authentication errors (login failures)
     */
    public static function logAuthError($message, $username = null)
    {
        $context = [
            'username' => $username ?? '',
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'user_agent' => Request::userAgent(),
            'ip' => Request::ip()
        ];

        ErrorLog::create([
            'level' => 'warning',
            'message' => $message ?? 'Authentication error',
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'file' => 'auth/login',
            'line' => 0,
            'stack_trace' => null,
            'ip_address' => Request::ip(),
            'username' => $username ?? '',
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'user_agent' => Request::userAgent()
        ]);
    }

    /**
     * Log form validation errors
     */
    public static function logValidationError($errors, $formName = null)
    {
        $context = [
            'form' => $formName ?? 'unknown_form',
            'errors' => $errors ?? [],
            'input' => self::filterSensitiveData(Request::all()),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'user_agent' => Request::userAgent(),
            'ip' => Request::ip(),
            'user_id' => Auth::id()
        ];

        ErrorLog::create([
            'level' => 'notice',
            'message' => "Validation failed for " . ($formName ?? 'unknown') . " form",
            'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
            'file' => Request::path(),
            'line' => 0,
            'stack_trace' => null,
            'ip_address' => Request::ip(),
            'user_id' => Auth::id(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'user_agent' => Request::userAgent()
        ]);
    }

    /**
     * Filter sensitive data from inputs
     */
    private static function filterSensitiveData($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password', 
            'oldpassword', 'newpassword', 'cnewpassword', 
            'secret', 'token', 'api_key'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '********';
            }
        }
        
        return $data;
    }
} 