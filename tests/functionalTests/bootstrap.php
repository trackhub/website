<?php

require __DIR__ .  '/../../config/bootstrap.php';

(function(string $dbDsn) {
    var_dump($dbDsn);
    // mysql://gps:1@sql:3306/gps
    preg_match('#^(?P<driver>[a-z]+)://(?P<user>[a-z]+):(?P<pass>[a-z0-9]+)@(?P<host>[a-z]+):(?P<port>[0-9]+)/(?P<db>[a-z]+)#', $dbDsn, $dsnParts);
    print_r($dsnParts);
    // mysql:dbname=testdb;host=127.0.0.1
    $pdo = new PDO("{$dsnParts['driver']}:dbname={$dsnParts['db']};host={$dsnParts['host']}", $dsnParts['user'], $dsnParts['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("UPDATE version SET file_id = NULL");
    $pdo->exec("UPDATE track_file SET version_id = NULL");
    $pdo->exec("DELETE FROM track_file");
    $pdo->exec("DELETE FROM `point`");
    $pdo->exec("DELETE FROM `optimized_point`");
    $pdo->exec("DELETE FROM `video_youtube`");
    $pdo->exec("DELETE FROM `track_slug`");
    $pdo->exec("DELETE FROM version_rating");
    $pdo->exec("DELETE FROM version");
    $pdo->exec("DELETE FROM place_image");
    $pdo->exec("DELETE FROM place");
    $pdo->exec("DELETE FROM track_image");
    $pdo->exec("DELETE FROM track");
    $pdo->exec("DELETE FROM `user`");




    echo "the end";
    die;


})($_ENV['DATABASE_URL']);