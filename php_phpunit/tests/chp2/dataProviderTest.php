<?php

class chp2_dataProvider extends PHPUnit_Framework_TestCase{
    public function provider(){
        return new HttpJsonProvider("http://10.0.0.0/xxx/");
    }

    /**
     * @dataProvider provider
     */
    public function testConsumer($a, $b, $sum){
        $this->assertEquals($sum, $a + $b);
    }
}

/**
 * 这是一个fack的provider。
 */
class HttpJsonProvider implements Iterator{
    private $_url = "";
    private $_var = array();

    public function __construct($url)
    {
        // $this->_url = $url;
        $this->_var = array(
            array(0,0,0),
            array(1,1,2),
            array(1,0,1),
            array(1,1,3), // Going to be failed.
        );
    }

    public function rewind() {
        //echo "rewinding\n";
        reset($this->_var);
    }

    public function current() {
        $var = current($this->_var);
        //echo "current: $var\n";
        return $var;
    }

    public function key() {
        $var = key($this->_var);
        //echo "key: $var\n";
        return $var;
    }

    public function next() {
        $var = next($this->_var);
        //echo "next: $var\n";
        return $var;
    }

    public function valid() {
        $var = $this->current() !== false;
        //echo "valid: {$var}\n";
        return $var;
    }
}
// 另请参考官网上，精辟的：
//  public function additionProvider()
//  {
//      return new CsvFileIterator('data.csv');
//  }

if(!debug_backtrace()){
    var_dump("命中！");die();

    $it = new HttpJsonProvider("");
    foreach ($it as $a => $b) {
        print "$a: $b\n";
    }
}
