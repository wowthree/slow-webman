<?php

namespace support\exception;

use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\Exception\ExceptionHandlerInterface;

class ApiHandler implements ExceptionHandlerInterface
{
    public int $code;
    public string $msg;
    public array $header;

    public function __construct(int $code = 400, string $msg = '未知错误', $header = [])
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->header = $header;
    }


    public function report(Throwable $exception)
    {
        Handler::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        if (!$request->expectsJson()) {
            return response($this->msg, $this->code, $this->header);
        }
        return json(['msg' => $this->msg, 'status' => $this->code]);
    }
}
