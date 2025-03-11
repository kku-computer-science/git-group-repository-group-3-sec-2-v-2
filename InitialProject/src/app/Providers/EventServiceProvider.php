<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Register a listener for handling form submissions
        Event::listen('form.submitted', function ($formName, $formData, $userId = null) {
            if (is_null($userId) && auth()->check()) {
                $userId = auth()->id();
            }
            
            // Skip logging if no user is authenticated
            if (is_null($userId)) {
                return;
            }
            
            // Filter sensitive data
            $filteredData = collect($formData)->except([
                'password', 'password_confirmation', 'current_password',
                'oldpassword', 'newpassword', 'cnewpassword', 
                '_token', '_method'
            ])->toArray();
            
            // Log the form submission
            \App\Models\ActivityLog::create([
                'user_id' => $userId,
                'action' => 'Submit ' . $formName,
                'description' => 'User submitted form: ' . $formName . ' with data: ' . json_encode($filteredData, JSON_UNESCAPED_UNICODE),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        });
    }
}
