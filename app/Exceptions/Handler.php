<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry') && $this->shouldReport($e)) {
                app('sentry')->captureException($e);
            }
        });
        
        // Handle API exceptions
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }
    
    /**
     * Handle API exceptions and return standardized JSON responses
     *
     * @param Throwable $exception
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleApiException(Throwable $exception, $request)
    {
        // Log the exception
        if ($this->shouldReport($exception)) {
            \Log::error($exception);
        }
        
        // Handle different exception types
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }
        
        if ($exception instanceof ValidationException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $exception->errors(),
            ], 422);
        }
        
        if ($exception instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($exception->getModel()));
            return response()->json([
                'status' => 'error',
                'message' => "Unable to find {$modelName} with the provided identifier",
            ], 404);
        }
        
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'The requested resource was not found',
            ], 404);
        }
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => 'error',
                'message' => 'The requested method is not allowed for this endpoint',
            ], 405);
        }
        
        // Default error response
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
        
        // In production, don't expose detailed error information
        $message = app()->environment('production') && $statusCode === 500
            ? 'An unexpected error occurred. Our team has been notified.'
            : $exception->getMessage();
            
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $statusCode);
    }
}
