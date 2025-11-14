<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
  protected $levels = [];
  protected $dontReport = [];
  protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

  public function register(): void
  {
    //
  }

  public function render($request, Throwable $exception)
  {
    // Código de error por defecto
    $status = 500;

    // Si es una excepción HTTP (404, 403, etc.)
    if ($this->isHttpException($exception) && $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
      $status = $exception->getStatusCode();
    }

    // Devuelve la misma vista para todos los errores
    return response()->view('errors/page-blank', ['code' => $status], $status);
  }
}
