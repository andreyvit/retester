<?php

include 'lib/prefix.inc.php';

$id = $_REQUEST['question_id'];
execute("DELETE FROM answers WHERE question_id = '%s'", $id);
execute("DELETE FROM questions WHERE id = '%s'", $id);
echo "Deleted $id.";

?>