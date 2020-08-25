<?php
if(!headers_sent()) {
  header("Content-Type: text/html; charset=UTF-8");
}
?>

<?php $this->display($request, $fw_template); ?>

