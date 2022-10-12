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

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jiannei\Response\Laravel\Support\Facades\Response;
use Jiannei\Response\Laravel\Tests\Repositories\Enums\ResponseCodeEnum;
use Jiannei\Response\Laravel\Tests\Repositories\Models\User;
use Jiannei\Response\Laravel\Tests\Repositories\Resources\UserCollection;
use Jiannei\Response\Laravel\Tests\Repositories\Resources\UserResource;

class SuccessTest extends TestCase
{
    use RefreshDatabase;

    public function testSuccess()
    {
        $response = Response::success();

        $this->assertEquals(200, $response->status());

        $expectedJson = json_encode([
            'status' => true,
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => null,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testCreated()
    {
        $response = Response::created();

        $this->assertEquals(201, $response->status());

        $expectedJson = json_encode([
            'status' => true,
            'code' => 201,
            'message' => ResponseCodeEnum::fromValue(201)->description,
            'data' => null,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testAccepted()
    {
        $response = Response::accepted();

        $this->assertEquals(202, $response->status());

        $expectedJson = json_encode([
            'status' => true,
            'code' => 202,
            'message' => ResponseCodeEnum::fromValue(202)->description,
            'data' => null,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testNoContent()
    {
        $response = Response::noContent();

        $this->assertEquals(204, $response->status());

        $expectedJson = json_encode([
            'status' => true,
            'code' => 204,
            'message' => ResponseCodeEnum::fromValue(204)->description,
            'data' => null,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithArrayData()
    {
        $data = [
            'name' => 'Jiannei',
            'email' => 'longjian.huang@foxmail.com',
        ];
        $response = Response::success($data);

        $this->assertEquals(200, $response->status());

        $expectedJson = json_encode([
            'status' => true,
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithResourceData()
    {
        $user = User::factory()->make();
        $response = Response::success(new UserResource($user));

        $this->assertEquals(200, $response->status());
        $expectedJson = json_encode([
            'status' => true,
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => [
                'nickname' => $user->name,
                'email' => $user->email,
            ],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithCollectionData()
    {
        $users = User::factory()->count(10)->make();
        $response = Response::success(new UserCollection($users));

        $this->assertEquals(200, $response->status());

        $data = $users->map(function ($item) {
            return [
                'nickname' => $item->name,
                'email' => $item->email,
            ];
        })->all();
        $expectedJson = json_encode([
            'status' => true,
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithPaginatedData()
    {
        User::factory()->count(20)->create();
        $users = User::paginate();

        $response = Response::success(new UserCollection($users));

        $this->assertEquals(200, $response->status());

        $paginated = $users->toArray();
        $formatData = $users->map(function ($item) {
            return [
                'nickname' => $item->name,
                'email' => $item->email,
            ];
        })->all();
        $data = [
            'data' => $formatData,
            'meta' => [
                'pagination' => [
                    'count' => $paginated['to'] ?? 0,
                    'per_page' => $paginated['per_page'] ?? 0,
                    'current_page' => $paginated['current_page'] ?? 0,
                    'total' => $paginated['total'] ?? 0,
                    'total_pages' => $paginated['last_page'] ?? 0,
                    'links' => [
                        'previous' => $paginated['prev_page_url'] ?? '',
                        'next' => $paginated['next_page_url'] ?? '',
                    ],
                ],
            ],
        ];
        $expectedJson = json_encode([
            'status' => true,
            'code' => 200,
            'message' => ResponseCodeEnum::fromValue(200)->description,
            'data' => $data['data'],
            'meta' => $data['meta']
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithMessage()
    {
        $response = Response::success(null, 'Success');

        $expectedJson = json_encode([
            'status' => true,
            'code' => 200,
            'message' => 'Success',
            'data' => null,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }

    public function testSuccessWithCustomMessageAndCode()
    {
        $response = Response::success(null, '', ResponseCodeEnum::SERVICE_LOGIN_SUCCESS);

        $expectedJson = json_encode([
            'status' => true,
            'code' => ResponseCodeEnum::SERVICE_LOGIN_SUCCESS,
            'message' => ResponseCodeEnum::fromValue(ResponseCodeEnum::SERVICE_LOGIN_SUCCESS)->description,
            'data' => null,
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, $response->getContent());
    }
}
