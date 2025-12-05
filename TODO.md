# TODO: Fix Authentication Error for API Routes

## Issue Description
The application is throwing a "Route [login] not defined" error when an unauthenticated request is made to protected API routes. Instead of returning an "unauthenticated" response, it's attempting to redirect to a login route that doesn't exist.

## Root Cause
The default Laravel `Authenticate` middleware is configured to redirect unauthenticated users to a 'login' route. For API routes using Sanctum, this should instead return a JSON response with an "unauthenticated" message, but the middleware is not recognizing API requests as expecting JSON responses.

## Steps to Fix
1. Create a custom `Authenticate` middleware that prevents redirection for API routes
2. Update `bootstrap/app.php` to use the custom middleware
3. Configure exception handling to return proper JSON responses for authentication failures
4. Test the fix to ensure API routes return 401 with "unauthenticated" message instead of redirecting

## Files to Modify
- `app/Http/Middleware/CustomAuthenticate.php` (create new)
- `bootstrap/app.php` (update middleware alias and exception handling)
