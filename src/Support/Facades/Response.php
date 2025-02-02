<?php

/*
 * This file is part of the jiannei/laravel-response.
 *
 * (c) Jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Jiannei\Response\Laravel\Support\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

/**
 * @method static JsonResponse|JsonResource accepted($data = null, string $message = '', string $location = '')
 * @method static JsonResponse|JsonResource created($data = null, string $message = '', string $location = '')
 * @method static noContent(string $message = '')
 * @method static JsonResponse|JsonResource localize(int $code = 200, array $headers = [], int $option = 0)
 * @method static JsonResponse|JsonResource ok(string $message = '', int $code = 200, array $headers = [], int $option = 0)
 * @method static JsonResponse|JsonResource success($data = null, string $message = '', int $code = 200, array $headers = [], int $option = 0)
 * @method static void errorBadRequest(?string $message = '')
 * @method static void errorUnauthorized(string $message = '')
 * @method static void errorForbidden(string $message = '')
 * @method static void errorNotFound(string $message = '')
 * @method static void errorMethodNotAllowed(string $message = '')
 * @method static void errorInternal(string $message = '')
 * @method static JsonResponse fail(string $message = '', int $code = 500, $errors = null, array $header = [], int $options = 0)
 *
 * @see \Jiannei\Response\Laravel\Response
 */
class Response extends IlluminateFacade
{
    protected static function getFacadeAccessor()
    {
        return \Jiannei\Response\Laravel\Response::class;
    }
}
