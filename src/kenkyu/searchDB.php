<?php
function searchDB($str_sql){//データベース接続用の関数
    $host = "localhost";
    $user = "Sotuken";
    $pass = "sotuken";
    $dbName = "sotuken3";

    $tableName = "";
    $db = mysqli_connect($host,$user,$pass,$dbName);
    if($db == false){
        die('接続できませんでした:');
        exit;
    }
    $rs = mysqli_query($db,$str_sql);
    mysqli_close($db);
    return $rs;
}
?>