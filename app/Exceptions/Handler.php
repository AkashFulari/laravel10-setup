<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
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
     */
    public function register(): void
    {
        $this->renderable(function (Exception $e, $request) {});
    }

    public function render($request, $e)
    {
        if ($request->is('api/*') || $request->is('admin/*')) {
            if ($e instanceof InvalidAccessException) {
                Log::info('Invalid Access Exception: ' . $e->getMessage());
                return response()->json([
                    'message' => $e->getMessage()
                ], 401);
            } else if ($e instanceof ActivationException) {
                Log::info('Activation Exception: ' . $e->getMessage());
                return response()->json([
                    'message' => $e->getMessage()
                ], 403);
            } else {
                return response()->json([
                    'message' => $e->getMessage(),
                    "from" => "handler Exception",
                ], 404);
            }
        }
        // Let Laravel handle other exceptions
        return parent::render($request, $e);
    }
}
