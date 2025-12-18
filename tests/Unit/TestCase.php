<?php

namespace Tests\Unit;

use Tests\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use InteractsWithContainer;
} 