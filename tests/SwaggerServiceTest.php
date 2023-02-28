<?php

namespace RonasIT\Support\Tests;

use Symfony\Component\HttpFoundation\Response;
use RonasIT\Support\AutoDoc\Services\SwaggerService;
use RonasIT\Support\AutoDoc\Exceptions\LegacyConfigException;
use RonasIT\Support\Tests\Support\Traits\SwaggerServiceMockTrait;
use RonasIT\Support\AutoDoc\Exceptions\InvalidDriverClassException;
use RonasIT\Support\AutoDoc\Exceptions\WrongSecurityConfigException;
use RonasIT\Support\AutoDoc\Exceptions\SwaggerDriverClassNotFoundException;

class SwaggerServiceTest extends TestCase
{
    use SwaggerServiceMockTrait;

    public function testConstructorInvalidConfigVersion()
    {
        config(['auto-doc.config_version' => '1.0']);

        $this->expectException(LegacyConfigException::class);

        app(SwaggerService::class);
    }

    public function testConstructorEmptyConfigVersion()
    {
        config(['auto-doc.config_version' => null]);

        $this->expectException(LegacyConfigException::class);

        app(SwaggerService::class);
    }

    public function testConstructorDriverClassNotExists()
    {
        config(['auto-doc.drivers.local.class' => 'NotExistsClass']);

        $this->expectException(SwaggerDriverClassNotFoundException::class);

        app(SwaggerService::class);
    }

    public function testConstructorDriverClassNotImplementsInterface()
    {
        config(['auto-doc.drivers.local.class' => TestCase::class]);

        $this->expectException(InvalidDriverClassException::class);

        app(SwaggerService::class);
    }

    public function getAddData(): array
    {
        return [
            [
                'contentType' => 'application/json',
                'requestFixture' => 'tmp_data_search_roles_request',
                'responseFixture' => 'example_success_roles_response.json',
            ],
            [
                'contentType' => 'application/pdf',
                'requestFixture' => 'tmp_data_search_roles_request_pdf',
                'responseFixture' => 'example_success_pdf_type_response.json',
            ],
            [
                'contentType' => 'text/plain',
                'requestFixture' => 'tmp_data_search_roles_request_plain_text',
                'responseFixture' => 'example_success_plain_text_type_response.json',
            ],
        ];
    }

    /**
     * @dataProvider getAddData
     *
     * @param string $contentType
     * @param string $requestFixture
     * @param string $responseFixture
     */
    public function testAddData(string $contentType, string $requestFixture, string $responseFixture)
    {
        $this->mockDriverSaveTmpData($this->getJsonFixture($requestFixture));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('get', 'users/roles', [
            'with' => ['users']
        ], [], [
            'Content-type' => 'application/json'
        ]);

        $response = new Response($this->getFixture($responseFixture), 200, [
            'Content-type' => $contentType,
            'authorization' => 'Bearer some_token'
        ]);

        $service->addData($request, $response);
    }

    public function testAddDataWithJWTSecurity()
    {
        config(['auto-doc.security' => 'jwt']);

        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_search_roles_request_jwt_security'));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('get', 'users/roles', [
            'with' => ['users']
        ]);

        $response = new Response($this->getFixture('example_success_roles_response.json'), 200, [
            'Content-type' => 'application/json',
            'authorization' => 'Bearer some_token'
        ]);

        $service->addData($request, $response);
    }

    public function testAddDataWithLaravelSecurity()
    {
        config(['auto-doc.security' => 'laravel']);

        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_search_roles_request_laravel_security'));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('get', 'users/roles', [
            'with' => ['users']
        ]);

        $response = new Response($this->getFixture('example_success_roles_response.json'), 200, [
            'Content-type' => 'application/json',
            'authorization' => 'Bearer some_token'
        ]);

        $service->addData($request, $response);
    }

    public function testAddDataWithEmptySecurity()
    {
        config(['auto-doc.security' => 'invalid']);

        $this->expectException(WrongSecurityConfigException::class);

        app(SwaggerService::class);
    }

    public function testAddDataWithPathParameters()
    {
        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_get_user_request'));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('get', 'users/{id}/assign-role/{role-id}', [
            'with' => ['role'],
            'with_likes_count' => true
        ], [
            'id' => 1,
            'role-id' => 5
        ]);

        $response = new Response($this->getFixture('example_success_user_response.json'), 200, [
            'Content-type' => 'application/json'
        ]);

        $service->addData($request, $response);
    }

    public function testAddDataClosureRequest()
    {
        config(['auto-doc.security' => 'jwt']);

        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_search_roles_closure_request'));

        $service = app(SwaggerService::class);

        $request = $this->generateClosureRequest('get', 'users/roles', [
            'with' => ['users']
        ]);

        $response = new Response($this->getFixture('example_success_roles_closure_response.json'), 200, [
            'Content-type' => 'application/json',
            'authorization' => 'Bearer some_token'
        ]);

        $service->addData($request, $response);
    }

    public function testAddDataPostRequest()
    {
        config(['auto-doc.security' => 'jwt']);

        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_post_request'));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('post', 'users', [
            'users' => [1,2],
            'query' => null
        ], [], [
            'authorization' => 'Bearer some_token'
        ]);

        $response = new Response($this->getFixture('example_success_users_post_response.json'), 200, [
            'Content-type' => 'application/json',
            'authorization' => 'Bearer some_token'
        ]);

        $service->addData($request, $response);
    }

    public function testCutExceptions()
    {
        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_create_user_request'));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('post', '/api/users', [
            'first_name' => 'andrey',
            'last_name' => 'voronin'
        ]);

        $response = new Response($this->getFixture('example_forbidden_user_response.json'), 403, [
            'Content-type' => 'application/json'
        ]);

        $service->addData($request, $response);
    }

    public function testLimitResponseData()
    {
        config(['auto-doc.response_example_limit_count' => 1]);

        $this->mockDriverSaveTmpData($this->getJsonFixture('tmp_data_search_users_request'));

        $service = app(SwaggerService::class);

        $request = $this->generateRequest('get', '/api/users');

        $response = new Response($this->getFixture('example_success_users_response.json'), 200, [
            'Content-type' => 'application/json'
        ]);

        $service->addData($request, $response);
    }
}