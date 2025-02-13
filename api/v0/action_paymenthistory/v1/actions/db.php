<?php
$pdo = null;

try {
    // Connection setup (same as before)
    $host = "c9pv5s2sq0i76o.cluster-czrs8kj4isg7.us-east-1.rds.amazonaws.com";
    $port = "5432";
    $dbname = "daed51holtaobg";
    $user = "u8u4vihlmmi3vp";
    $password = "p4fea8fae06183fb14e0ebc8b597b63a4d16021479234493f07e4972bdf60d7c8";
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>
