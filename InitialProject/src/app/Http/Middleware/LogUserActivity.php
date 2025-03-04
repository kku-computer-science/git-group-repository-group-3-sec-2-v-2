<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Only log if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Get route name or path
            $routeName = $request->route() ? ($request->route()->getName() ?? 'unnamed route') : 'unknown route';
            $path = $request->path();
            
            // Get request method
            $method = $request->method();
            
            // Determine action type based on method and path
            $actionType = $this->determineActionType($method, $path);
            
            // Create action and description
            $action = "{$actionType} {$routeName}";
            $description = "User performed {$actionType} operation on {$path}";
            
            // Add request parameters to description if it's a POST/PUT/PATCH request
            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                // Filter out sensitive data like passwords
                $filteredInput = collect($request->input())->except(['password', 'password_confirmation', 'oldpassword', 'newpassword', 'cnewpassword'])->toArray();
                if (!empty($filteredInput)) {
                    $inputData = json_encode($filteredInput, JSON_UNESCAPED_UNICODE);
                    $description .= " with data: {$inputData}";
                }
            }
            
            // Log the activity
            ActivityLog::log($user->id, $action, $description);
        }
        
        return $response;
    }
    
    /**
     * Determine the type of action based on HTTP method and path
     * 
     * @param string $method
     * @param string $path
     * @return string
     */
    private function determineActionType($method, $path)
    {
        if ($method === 'GET') {
            return 'View';
        }
        
        if ($method === 'POST') {
            if (strpos($path, 'create') !== false || strpos($path, 'store') !== false) {
                return 'Create';
            }
            
            if (strpos($path, 'upload') !== false) {
                return 'Upload';
            }
            
            return 'Submit';
        }
        
        if ($method === 'PUT' || $method === 'PATCH') {
            return 'Update';
        }
        
        if ($method === 'DELETE') {
            return 'Delete';
        }
        
        return $method;
    }
} 