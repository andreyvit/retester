<?php // phpinfo(); ?>
<pre>
<?

class Bar {
  function xxx() {
    $x = debug_backtrace();
    var_dump($x);
    $classname = $x[1]['class'];
    echo $classname;
  }
}

class Foo extends Model {
  
  var $table_name = "tests";

}

Foo::query(array(""));
Foo::xxx(10, 20);

?>