<?php

namespace Tests\Unit;

use App\Casts\MoneyCast;
use App\Models\RfidCard;
use PHPUnit\Framework\TestCase;

class MoneyCastTest extends TestCase
{
    private MoneyCast $cast;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast = new MoneyCast();
    }

    private function get(mixed $value): mixed
    {
        return $this->cast->get(new RfidCard(), 'balance', $value, []);
    }

    private function set(mixed $value): mixed
    {
        return $this->cast->set(new RfidCard(), 'balance', $value, []);
    }

    public function test_get_converts_cents_to_reais(): void
    {
        $this->assertEquals(9.90, $this->get(990));
    }

    public function test_get_converts_zero(): void
    {
        $this->assertEquals(0, $this->get(0));
    }

    public function test_get_converts_large_value(): void
    {
        $this->assertEquals(1000.00, $this->get(100000));
    }

    public function test_set_converts_reais_to_cents(): void
    {
        $this->assertEquals(990, $this->set(9.90));
    }

    public function test_set_converts_zero(): void
    {
        $this->assertEquals(0, $this->set(0));
    }

    public function test_set_converts_large_value(): void
    {
        $this->assertEquals(100000, $this->set(1000.00));
    }

    public function test_roundtrip_preserves_value(): void
    {
        $original = 49.99;
        $stored = $this->set($original);
        $retrieved = $this->get($stored);
        $this->assertEquals($original, $retrieved);
    }
}