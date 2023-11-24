<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ], 404, [], JSON_UNESCAPED_SLASHES);
            }
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'ok' => false,
                'message' => __('Unauthenticated.')
            ], 401, [], JSON_UNESCAPED_SLASHES);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'ok' => false,
            'message' => $exception->getMessage(),
            'errors' => array_map(function($field, $errors) {
                return [
                    'path' => $field,
                    'message' => implode(' ', $errors)
                ];
            }, array_keys($exception->errors()), $exception->errors()),
        ], $exception->status);
    }
}
