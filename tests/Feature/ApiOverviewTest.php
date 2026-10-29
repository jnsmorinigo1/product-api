<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiOverviewTest extends TestCase
{
    public function test_the_root_describes_the_api(): void
    {
        $this->getJson('/')
            ->assertOk()
            ->assertJsonPath('name', 'Product API')
            ->assertJsonStructure(['name', 'documentation', 'openapi']);
    }

    public function test_the_openapi_document_is_served(): void
    {
        $response = $this->get('/docs/openapi.yaml');

        $response->assertOk();
        $this->assertStringContainsString('application/yaml', (string) $response->headers->get('content-type'));
        $response->assertSee('openapi: 3.0.3', false);
    }

    public function test_swagger_ui_is_served_from_the_application(): void
    {
        $response = $this->get('/docs');

        $response->assertOk()
            ->assertSee('vendor/swagger-ui/swagger-ui-bundle.js', false)
            ->assertSee('vendor/swagger-ui/swagger-ui.css', false)
            ->assertDontSee('unpkg.com', false);

        $policy = (string) $response->headers->get('content-security-policy');
        $this->assertStringContainsString("script-src 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
    }
}
