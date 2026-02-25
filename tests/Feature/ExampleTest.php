<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_200(): void
    {
        $this->seed();
        $this->get('/')->assertOk();
    }

    public function test_fr_returns_200(): void
    {
        $this->seed();
        $this->get('/fr')->assertOk();
    }
}
