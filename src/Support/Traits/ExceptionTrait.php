<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Traits;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Throwable;

trait ExceptionTrait
{
    /**
     * Custom Normal Exception response.
     *
     * @param $request
     * @param  Throwable|Exception  $e
     * @return JsonResponse
     */
    protected function prepareJsonResponse($request, $e)
    {
        // 要求请求头 header 中包含 /json 或 +json，如：Accept:application/json
        // 或者是 ajax 请求，header 中包含 X-Requested-With：XMLHttpRequest;
        $exceptionConfig = Arr::get(Config::get('response.exception'), get_class($e));
        $isHttpException = $this->isHttpException($e);

        $message = $exceptionConfig['message'] ?? ($isHttpException ? $e->getMessage() : 'Server Error');
        $code = $exceptionConfig['code'] ?? ($isHttpException ? $e->getStatusCode() : 500);
        $header = $exceptionConfig['header'] ?? ($isHttpException ? $e->getHeaders() : []);
        $options = $exceptionConfig['options'] ?? (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return Response::fail($message, $code, $this->convertExceptionToArray($e), $header, $options);
    }

    /**
     * Custom Failed Validation Response for Lumen.
     *
     * @param  Request  $request
     * @param  array  $errors
     * @return mixed
     *
     * @throws HttpResponseException
     */
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        if (isset(static::$responseBuilder)) {
            return (static::$responseBuilder)($request, $errors);
        }

        $firstMessage = Arr::first($errors, null, '');

        return Response::fail(
            is_array($firstMessage) ? Arr::first($firstMessage) : $firstMessage,
            Arr::get(Config::get('response.exception'), ValidationException::class.'.code', 422),
            $errors
        );
    }

    /**
     * Custom Failed Validation Response for Laravel.
     *
     * @param  Request  $request
     * @param  ValidationException  $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return Response::fail(
            $exception->getMessage(),
            Arr::get(Config::get('response.exception'), ValidationException::class.'.code', 422),
            ['fields' => $exception->errors()]
        );
    }

    /**
     * Custom Failed Authentication Response for Laravel.
     *
     * @param  Request  $request
     * @param  AuthenticationException  $exception
     * @return \Illuminate\Http\RedirectResponse | JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $exceptionConfig = Arr::get(Config::get('response.exception'), AuthenticationException::class);

        return $request->expectsJson()
            ? Response::errorUnauthorized($exceptionConfig['message'] ?? $exception->getMessage())
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
}
