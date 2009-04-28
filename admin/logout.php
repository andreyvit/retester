<?php
header('WWW-Authenticate: Basic realm="re:tester"');
header('HTTP/1.0 401 Unauthorized');
header('Location: index.php');
?>
