<?php
    $config = require_once './config.php';

    try {
        $pdo = new \PDO(
            sprintf('mysql:host=%s;dbname=%s', $config['host'], $config['dbname']),
            $config['user'],
            $config['pass'],
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)
        );
    } catch (PDOException $e) {
        echo 'Error connection: ' . $e->getMessage();
        exit;
    }
