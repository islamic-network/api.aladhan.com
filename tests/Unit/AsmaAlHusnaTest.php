<?php

namespace Tests\Unit;


use PHPUnit\Framework\TestCase;
use Api\Models\AsmaAlHusna;

class AsmaAlHusnaTest extends TestCase
{
    public AsmaAlHusna $x;
    public function setUp(): void
    {
        parent::setUp();
        $this->x = new AsmaAlHusna();

    }

    public function testIsValidNumber()
    {
        $this->assertTrue($this->x->isValidNumber(77));
        $this->assertFalse($this->x->isValidNumber(777));
        $this->assertFalse($this->x->isValidNumber(-1));
        $this->assertFalse($this->x->isValidNumber(0));
    }

    public function testExtract()
    {
        $this->assertEmpty($this->x->extract([777]));
        $this->assertNotEmpty($this->x->extract([77]));
        $this->assertCount(3, $this->x->extract([77, 88, 99]));
    }
}