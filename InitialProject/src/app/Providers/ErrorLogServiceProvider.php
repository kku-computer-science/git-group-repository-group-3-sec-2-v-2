<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Models\ErrorLog;
use Illuminate\Log\Events\MessageLogged;

class ErrorLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Listen for log events
        $this->app['events']->listen(MessageLogged::class, function (MessageLogged $event) {
            // Only log errors and warnings
            if (in_array($event->level, ['error', 'warning', 'critical', 'alert', 'emergency'])) {
                try {
                    // Extract file and line from context if available
                    $file = $event->context['file'] ?? null;
                    $line = $event->context['line'] ?? null;
                    
                    // Extract exception trace if available
                    $trace = null;
                    if (isset($event->context['exception'])) {
                        $exception = $event->context['exception'];
                        $trace = $exception->getTraceAsString();
                    }
                    
                    // Create error log entry
                    ErrorLog::create([
                        'level' => $event->level,
                        'message' => $event->message,
                        'context' => json_encode($event->context),
                        'file' => $file,
                        'line' => $line,
                        'trace' => $trace,
                    ]);
                } catch (\Exception $e) {
                    // Prevent infinite loop by not logging errors that occur during logging
                    Log::channel('single')->error('Failed to log error to database: ' . $e->getMessage());
                }
            }
        });
    }
} 