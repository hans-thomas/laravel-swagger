<?php

namespace RonasIT\Support\Tests;

use Illuminate\Http\Response;

class AutoDocControllerTest extends TestCase
{
    protected $documentation;
    protected $localDriverFilePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->localDriverFilePath = __DIR__ . '/../storage/documentation.json';
        $this->documentation = $this->getJsonFixture('tmp_data');

        file_put_contents($this->localDriverFilePath, json_encode($this->documentation));

        config(['auto-doc.drivers.local.production_path' => $this->localDriverFilePath]);
    }

    public function testGetJSONDocumentation()
    {
        $response = $this->json('get', '/auto-doc/documentation');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson($this->documentation);
    }

    public function testGetJSONDocumentationWithGlobalPrefix()
    {
        $this->addGlobalPrefix();

        $response = $this->json('get', '/global/auto-doc/documentation');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson($this->documentation);
    }

    public function testGetViewDocumentation()
    {
        config(['auto-doc.display_environments' => ['testing']]);

        $response = $this->get('/');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals($response->getContent(), $this->getFixture('rendered_documentation.html'));
    }

    public function testGetViewDocumentationEnvironmentDisable()
    {
        $response = $this->get('/');

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        $response->assertSeeText('Forbidden.');
    }

    public function testGetViewDocumentationWithGlobalPrefix()
    {
        $this->addGlobalPrefix();

        config(['auto-doc.display_environments' => ['testing']]);

        $response = $this->get('/global');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals($response->getContent(), $this->getFixture('rendered_documentation_with_global_path.html'));
    }
}
