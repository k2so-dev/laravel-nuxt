<?php

use App\Http\Middleware\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->throttleApi(redis: true)
            ->trustProxies(at: [
                '127.0.0.1',
            ])
            ->api(prepend: [
                JsonResponse::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /*
         * Format not found responses
         */
        $exceptions->render(static function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api*')) {
                return response()->json([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ], $e->getStatusCode(), [], JSON_UNESCAPED_SLASHES);
            }
        });

        /*
         * Format unauthorized responses
         */
        $exceptions->render(static function (AuthenticationException $e, Request $request): \Illuminate\Http\JsonResponse | \Illuminate\Http\RedirectResponse {
            if ($request->is('api*')) {
                return response()->json([
                    'ok' => false,
                    'message' => __('Unauthenticated.'),
                ], 401, [], JSON_UNESCAPED_SLASHES);
            }

            return redirect()->guest(route('login'));
        });

        /*
         * Format validation errors
         */
        $exceptions->render(static function (ValidationException $e, Request $request): \Illuminate\Http\JsonResponse {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'errors' => array_map(static function (string $field, array $errors): array {
                    return [
                        'path' => $field,
                        'message' => implode(' ', $errors),
                    ];
                }, array_keys($e->errors()), $e->errors()),
            ], $e->status);
        });
    })->create();
