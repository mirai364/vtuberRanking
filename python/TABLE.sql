CREATE DATABASE IF NOT EXISTS vtuber_database;
USE vtuber_database;

CREATE TABLE `channel` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `channelId` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `thumbnail` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `channelName` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `group` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `createdAt` DATETIME NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `channelId` (`channelId`) USING BTREE
)
COLLATE='utf8mb4_0900_ai_ci'
ENGINE=InnoDB;

CREATE TABLE `channelData` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `channelId` INT(10) NOT NULL DEFAULT '0',
    `subscribers` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `play` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
    `createdAt` DATETIME NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `channelId` (`channelId`) USING BTREE
)
COLLATE='utf8mb4_0900_ai_ci'
ENGINE=InnoDB;

CREATE TABLE `concurrentViewers` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `videoId` INT(10) UNSIGNED NOT NULL,
    `viewers` MEDIUMINT(7) UNSIGNED NOT NULL DEFAULT '0',
    `createdAt` DATETIME NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `videoId` (`videoId`) USING BTREE
)
COLLATE='utf8mb4_0900_ai_ci'
ENGINE=InnoDB;

CREATE TABLE `video` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `channelId` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `videoId` VARCHAR(32) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `videoName` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
    `starttime` DATETIME NULL DEFAULT NULL,
    `isAlive` TINYINT(3) UNSIGNED NOT NULL,
    `updatedAt` DATETIME NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `videoId` (`videoId`) USING BTREE,
    INDEX `videoId_isAlive` (`videoId`, `isAlive`) USING BTREE
)
COLLATE='utf8mb4_0900_ai_ci'
ENGINE=InnoDB;
