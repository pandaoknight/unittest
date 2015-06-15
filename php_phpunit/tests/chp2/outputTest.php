<?php

class ch2_output extends PHPUnit_Framework_TestCase{
    /**
     * 注意观察PHPUnit打印出来的"diff"
     */
    public function testStringFail(){
        $this->expectOutputString("poo");
        print 'foo';
    }

    /**
     * 一个简单测试中国手机号的Regex
     */
    public function testRegex(){
        $this->expectOutputRegex("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/");

        print "+（86）13500000000";
    }

    /**
     * 这是一个用dataProvider进行Data Driven Test的思路，并带有可驱动的异常。
     * 但一般建议把正常的流 和 异常的流分在两个provider中。
     *
     * @dataProvider provider
     */
    public function testDataProvider($a, $b, $output, $exception){
        if(isset($output))
            $this->expectOutputString($output);
        if(!empty($exception)){
            $this->setExpectedException($exception);
        }

        if(!is_int($a))
            throw new Exception("none int a");
        print $a + $b;
    }

    public function provider(){
        return array(
            array(0,0,"0",""),
            array(1,0,"1",""),
            array(1,0,"0",""),
            array("a",0,null,"Exception"),
            array("a",0,null,"NonExistException"),
        );
    }
}
