<?php
require_once '../include/topscripts.php';

$id = filter_input(INPUT_GET, 'id');
$text = addslashes(filter_input(INPUT_GET, 'text'));
$error = '';
$result = '';

$sql = "update sparepart set comment = '$text' where id = $id";
$executer = new Executer($sql);
$error = $executer->error;

if(empty($error)) {
    $sql = "select comment from sparepart where id = $id";
    $fetcher = new Fetcher($sql);
    if($row = $fetcher->Fetch()) {
        $result = $row[0];
    }
    else {
        $result = "Ошибка при чтении примечания из базы";
    }
}
else {
    $result = $error;
}

echo $result;
?>