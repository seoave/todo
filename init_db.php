<?php
    /**
     * create DB
     */

    // create table users
    $sql = <<<'SQL'
CREATE TABLE  IF NOT EXISTS `users` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR (255) NOT NULL,
	`pass` VARCHAR(19) NOT NULL,  
	PRIMARY KEY (`id`)
);
SQL;
    $pdo->exec($sql);

    // create table lists
    $sql = <<<'SQL'
CREATE TABLE  IF NOT EXISTS `lists` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME,
	`list_name` VARCHAR (255) NOT NULL,
	`user_id` INT(10) UNSIGNED NOT NULL,  
	PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users (`id`)
);
SQL;
    $pdo->exec($sql);

    // create table tasks
    $sql = <<<'SQL'
CREATE TABLE  IF NOT EXISTS `tasks` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`created_at` DATETIME,
	`title` VARCHAR (255) NOT NULL,
    `is_done` INT(1) NOT NULL,
	`list_id` INT(10) UNSIGNED NOT NULL,  
	PRIMARY KEY (`id`),
    FOREIGN KEY (`list_id`) REFERENCES lists (`id`)
);
SQL;
    $pdo->exec($sql);

