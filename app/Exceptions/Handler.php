<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
     * A list of the inputs that are never flashed for validation exceptions.
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

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            if (! $request->expectsJson()) {
                if ( \Auth::guest() ) {
                    return redirect()->to('crm/adminlogin');
                }
            }else{
                $statuscode = $exception->getStatusCode();
                $message = "Not found";
                return response()->json(['status'=>$statuscode,'error'=> $message]);
            }
        }
        if ($this->isHttpException($exception)) {
            if ($exception->getStatusCode() == 403) {
                return response()->view('crm.errors.' . '403', [], 403);
            }
            if ($exception->getStatusCode() == 404) {
                return response()->view('crm.errors.' . '404', [], 404);
            }
            
            if ($exception->getStatusCode() == 500) {
                return response()->view('crm.errors.' . '500', [], 500);
            }

            if ($exception->getStatusCode() == 400) {
                return response()->view('crm.errors.' . '400', [], 400);
            }
        }
        return parent::render($request, $exception);
    }
}