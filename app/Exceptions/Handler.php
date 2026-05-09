<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // This forces JSON for ALL exceptions in API routes
        if ($request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api/')) {
            // Optional: make it nicer with status code
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            return response()->json([
                'status'  => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'error'   => $e->getMessage(),
                // 'trace'   => $e->getTraceAsString(),   // ← uncomment only in dev!
            ], $status);
        }

        return parent::render($request, $e);
    }
}
