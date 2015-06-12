<?php

class ch2_depends extends PHPUnit_Framework_TestCase{
    public function testFirst(){
        $stack = array();
        $stack[] = "first";
        return $stack;
    }

    /**
     * @depends testFirst
     */
    public function testSecond($stack){
        $this->assertEquals(1, sizeof($stack));   // 在减少重复计算的过程中，其实也会形成test之间的耦合。
        $stack[] = "second";
        return $stack;
    }

    /**
     * @depends testFirst
     * @depends testSecond
     */
    public function testThird($first, $second){
        var_dump($first);
        var_dump($second);
        $this->assertEquals("first", $first[0]);
        $this->assertEquals(2, sizeof($second));
    }
}
