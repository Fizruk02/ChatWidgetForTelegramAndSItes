<?php
require $_SERVER['DOCUMENT_ROOT'].'/admin/functions/functions.php';
require __DIR__.'/methods.php';
if(!method_exists(($cl = new cl($_POST)), ($method = $_GET['q']))) return response_if_error('метод '.$method.' не найден');
echo is_array( $resp =  $cl->$method( $_POST ) ) ? json_encode( $resp ) : $resp;