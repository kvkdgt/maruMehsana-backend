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

        // When the uploaded files exceed PHP's post_max_size, PHP drops the whole
        // request before validation runs. Turn that hard crash into a friendly message.
        $this->renderable(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            $limit = ini_get('post_max_size');
            $message = "The images you tried to upload are too large (server limit is {$limit} per submit). "
                . "Please choose smaller images or upload fewer images at a time.";

            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 413);
            }

            return redirect()->back()->with('error', $message);
        });
    }
}
