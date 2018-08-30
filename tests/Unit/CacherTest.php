<?php
use AlAdhanApi\Helper\Cacher;

class CacherTest extends \PHPUnit\Framework\TestCase
{
    private $mc;

    public function setUp()
    {
        $this->mc = new Cacher('127.0.0.1', 11211);
    }

    public function testNonExistentKey()
    {
        $this->assertFalse($this->mc->check('blah'));
        $this->assertFalse($this->mc->get('blue'));

    }

    public function testBooleanTrueKey()
    {
        $this->mc->set('one', true);
        $this->assertTrue($this->mc->check('one'));
        $this->assertTrue($this->mc->get('one'));
    }

    public function testBooleanFalseKey()
    {
        $this->mc->set('one', false);
        $this->assertTrue($this->mc->check('one'));
        $this->assertFalse($this->mc->get('one'));
    }

}
