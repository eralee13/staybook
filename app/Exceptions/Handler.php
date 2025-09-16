<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
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

    public function register(): void
    {
        //
    }

    // ↓↓↓ Ваш кастомный маппинг ошибок ↓↓↓
    public function render($request, Throwable $e)
    {
        // бизнес-ошибки = 400
        if ($e instanceof EtgBadRequestException) {
            return response()->json([
                'code'    => $e->codeInt,
                'message' => $e->getMessage(),
            ], 400);
        }

        // валидационные = 400 (валидатор ETG этого ждёт)
        if ($e instanceof ValidationException) {
            return response()->json([
                'code'    => 6,
                'message' => 'Invalid request: '.$e->getMessage(),
            ], 400);
        }

        // остальное по умолчанию
        return parent::render($request, $e);
    }
}