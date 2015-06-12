<?php

class ch2_expectedException extends PHPUnit_Framework_TestCase{
    /**
     * @expectedException
     * @expectedException Exception
     */
    public function testException(){
        // 已经测试 @expectedException标注一定需要有一个{确切的Exception名字}，不能如第一行默认置空。
        // *注：从另一方面来讲一个模糊的Exception捕获其实没有任何意义。
        // **另：我们知道exception机制是一个好的机制，然而我们很难用好自定义exception。是因为我们没有单测！
        throw new InvalidArgumentException("Some message", 1000);
    }

    /**
     */
    //public function testInvalidArgumentException($first){
        // 这个test会直接跪，而且加了@expectedException 也是没有用的。
    //}

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidArgumentException_pass(){
        throw new InvalidArgumentException("Some message", 1000);
    }

    /**
     * @expectedException Exception
     * @_expectedExceptionMessage Right Message
     * @_expectedExceptionMessageRegExp #Right*?#
     * @expectedExceptionCode 10
     */
    public function testExcpetionEx(){
        // 经过测试：我们发现 @expectedExceptionMessage标注 必须要配合@expectedException一起使用。不然不会起效！

        throw new Exception("Some Message");
        //throw new Exception("Right Message");
    }

    /**
     * 一个拥有绕口的名字的测试：
     *   在数据驱动测试(DDT)时以注入方式测试预期异常的测试
     *
     *   官方文档参考：
     *   void setExpectedException(string $exceptionName[, string $exceptionMessage = '', integer $exceptionCode = NULL])
     *   void setExpectedExceptionRegExp(string $exceptionName[, string $exceptionMessageRegExp = '', integer $exceptionCode = NULL])
     *   String getExpectedException()
     *
     * @dataProvider provider
     */
    public function testDataDrivenTestWithRegisterExpectedException($f, $b, $exceptionName){
        if(!empty($exceptionName))
            $this->setExpectedException($exceptionName);

        if(empty($f))
            throw new FooException();
        if(empty($b))
            throw new BarException();
    }

    /**
     * for testDataDrivenTestWithRegisterExpectedException
     */
    public function provider(){
        return array(
            array(false, 1024, "FooException"),
            array("test", false, "BarException"),
            array("test", 1024, ""),
            array("Will fail", 500, "FooException"),
        );
    }
}

/**
 * for testDataDrivenTestWithRegisterExpectedException
 */
class FooException extends Exception{}

class BarException extends Exception{}
