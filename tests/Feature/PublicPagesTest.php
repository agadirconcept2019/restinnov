<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_home_available(): void
    {
        $this->get('/')->assertOk();
    }
}
