<?php

class SubwayTest extends PHPUnit_Framework_TestCase{
    public function testGetAll(){
        $s = new Subway();
        $this->assertEquals(array(), $s->getAll());
    }
}
