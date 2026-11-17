<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the root path redirects to login for unauthenticated visitors.
     */
    public function test_root_path_redirects_unauthenticated_visitor(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
