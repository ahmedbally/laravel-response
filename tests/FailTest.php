<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Tests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;
use Jiannei\Response\Laravel\Tests\Repositories\Enums\ResponseCodeEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class FailTest extends TestCase
{
    use ExceptionTrait;

    public function testFail()
    {
        try {
            Response::fail();
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();

            $this->assertEquals(500, $response->getStatusCode());

            $expectedJson = json_encode([
                'status' => false,
                'code' => 500,
                'message' => ResponseCodeEnum::fromValue(500)->description,
                'data' => null,
            ]);

            $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
        }
    }

    public function testFailWithMessage()
    {
        try {
            Response::fail('Server Error');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();

            $this->assertEquals(500, $response->getStatusCode());

            $expectedJson = json_encode([
                'status' => false,
                'code' => 500,
                'message' => 'Server Error',
                'data' => null,
            ]);
            $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
        }
    }

    public function testFailWithCustomCodeAndMessage()
    {
        try {
            Response::fail('', ResponseCodeEnum::SERVICE_LOGIN_ERROR);
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();

            $this->assertEquals(500, $response->getStatusCode());

            $expectedJson = json_encode([
                'status' => false,
                'code' => ResponseCodeEnum::SERVICE_LOGIN_ERROR,
                'message' => ResponseCodeEnum::fromValue(ResponseCodeEnum::SERVICE_LOGIN_ERROR)->description,
                'data' => null,
            ]);
            $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
        }
    }

    public function testFailOutController()
    {
        try {
            abort(ResponseCodeEnum::SYSTEM_ERROR);
        } catch (HttpException $httpException) {
            $response = Response::fail(
                '',
                $this->isHttpException($httpException) ? $httpException->getStatusCode() : 500,
                $this->convertExceptionToArray($httpException),
                $this->isHttpException($httpException) ? $httpException->getHeaders() : [],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            );

            $expectedJson = json_encode([
                'status' => false,
                'code' => ResponseCodeEnum::SYSTEM_ERROR,
                'message' => ResponseCodeEnum::fromValue(ResponseCodeEnum::SYSTEM_ERROR)->description,
                'data' => $this->convertExceptionToArray($httpException),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
        }
    }

    /**
     * Determine if the given exception is an HTTP exception.
     *
     * @param  Throwable  $e
     * @return bool
     */
    protected function isHttpException(Throwable $e)
    {
        return $e instanceof HttpExceptionInterface;
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        return config('app.debug', false) ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
}
