<?php
/// 官网不建议使用 __autoload()
// spl_autoload_register() 提供了一种更加灵活的方式来实现类的自动加载。因此，不再建议使用 __autoload() 函数，在以后的版本中它可能被弃用。
//
/// 而且我们还有：
//      psr-0
//      psr-4
//  这样的事实标准
//
function __autoload($class_name) {
        require_once $class_name . '.php';
}

