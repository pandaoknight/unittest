<?php
include_once(__DIR__ . "/autoload.php");


class Subway{
    public function getAll(){
        $l = new SubwayLine();
        $s = new SubwayStation();
        return array(
            'lines' => $l,
            'stations' => $s,
        );
    }
}


/// Inline-test
if(!debug_backtrace()){
    $subway = new Subway();
    var_dump($subway->getAll());
}
