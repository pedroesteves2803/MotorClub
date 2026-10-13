<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'features.garage' => true,
            'features.shots' => true,
            'features.explore' => true,
            'features.ai_reports' => true,
            'features.workshops' => true,
            'features.events' => true,
            'features.subscriptions' => true,
            'features.demo' => true,
        ]);
    }
}
