<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SafeStrLimitTest extends TestCase
{
    public function test_safe_str_limit_returns_empty_string_for_null(): void
    {
        $this->assertSame('', safe_str_limit(null, 30));
    }

    public function test_safe_str_limit_preserves_laravel_truncation_behavior(): void
    {
        $this->assertSame('abcdefghijklmnopqrst...', safe_str_limit('abcdefghijklmnopqrstuvwxyz', 20));
    }
}
