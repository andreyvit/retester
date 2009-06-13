<?php phpinfo(); ?>
<pre>
<?

// available natively in PHP 5.3+, replacement from Phuby (http://github.com/shuber/phuby/)
if (!function_exists('get_called_class')) {
    function get_called_class() { 
        $backtrace = debug_backtrace();
        if (preg_match('/eval\(\)\'d code$/', $backtrace[1]['file'])) {
            return $backtrace[3]['args'][0];
        } else {
            $lines = file($backtrace[1]['file']);
            preg_match('/([a-zA-Z0-9\_]+)::'.$backtrace[1]['function'].'/', $lines[$backtrace[1]['line']-1], $matches);
            return $matches[1];
        }
    }
}

?>