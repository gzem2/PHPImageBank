<?php

declare(strict_types=1);

namespace PHPImageBank\tests\App;

use PHPUnit\Framework\TestCase;

use PHPImageBank\App\Model;
use PHPImageBank\App\ModelCollection;

/**
 * @covers ModelCollection
 */
final class ModelCollectionTest extends TestCase
{
    public $mc;
    public $m;

    public function setUp(): void
    {
        $this->mc = new ModelCollection();
        $this->m = new Model();
    
    }

    public function testAdd(): void
    {
        $this->mc->add($this->m);
        $this->assertEquals($this->m, $this->mc->get(0));
    }

    public function testGet(): void
    {
        $m2 = Model::fromRow(["data"=>2]);
        $this->mc->add($this->m);
        $this->mc->add($m2);
        $this->assertEquals(2, $this->mc->get(1)->data);
    }

    public function testOne(): void
    {
        $this->assertFalse($this->mc->one());

        $this->mc->add($this->m);
        $this->assertEquals($this->m, $this->mc->get(0));
    }

    public function testData(): void
    {
        $this->mc->add($this->m);
        $this->assertEquals([$this->m], $this->mc->data());
    }
}
