<?php

namespace App\Exceptions;

use App\Models\ErrorLog;
use App\Services\ErrorLogService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // Uncomment if you don't want these exceptions in logs
        // \Illuminate\Auth\AuthenticationException::class,
        // ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'oldpassword',
        'newpassword',
        'cnewpassword',
        'current_password',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        // Log all validation exceptions
        $this->reportable(function (ValidationException $e) {
            $formName = request()->path();
            ErrorLogService::logValidationError($e->errors(), $formName);
            
            return false; // Continue handling the exception normally
        });
        
        // Log 404 errors
        $this->reportable(function (NotFoundHttpException $e) {
            $context = [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip()
            ];
            
            ErrorLog::create([
                'level' => 'warning',
                'message' => 'Page not found: ' . request()->path(),
                'context' => json_encode($context, JSON_UNESCAPED_UNICODE),
                'file' => request()->path(),
                'line' => 0,
                'trace' => $e->getTraceAsString()
            ]);
            
            return false; // Continue handling the exception normally
        });
        
        // Log all other exceptions
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e)) {
                ErrorLogService::logException($e);
            }
            
            return false; // Continue handling the exception normally
        });
    }
}
