CREATE TABLE `user` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `email` TEXT NOT NULL UNIQUE,
    `name` TEXT NOT NULL
);

CREATE TABLE `pj` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `rep` INTEGER NOT NULL,
    `name` TEXT NOT NULL UNIQUE,
    `nickname` TEXT NOT NULL UNIQUE,
    `max_rsv` INTEGER NOT NULL,
    FOREIGN KEY (`rep`) REFERENCES `user`(`id`) ON DELETE CASCADE
);

CREATE TABLE `pj_roster` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `pj_id` INTEGER NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pj_id`) REFERENCES `pj`(`id`) ON DELETE CASCADE
);

CREATE TABLE `room` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL,
    `short_name` TEXT NOT NULL
);

CREATE TABLE `rsv` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `pj_id` INTEGER NOT NULL,
    `room_id` INTEGER NOT NULL,
    `user_id` INTEGER NOT NULL,
    `start_at` DATETIME NOT NULL,
    `finish_at` DATETIME NOT NULL,
    `note` TEXT,
    `rsved_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`pj_id`) REFERENCES `pj`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `room`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
    CHECK (`start_at` < `finish_at`)
);

CREATE TABLE `blackout_definition` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL,
    `start_apply_at` DATETIME NOT NULL,
    `finish_apply_at` DATETIME NOT NULL,
    CHECK (`start_apply_at` < `finish_apply_at`)
);

CREATE TABLE `room_blackout` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `definition_id` INTEGER NOT NULL,
    `room_id` INTEGER NOT NULL,
    `start_at` DATETIME NOT NULL,
    `finish_at` DATETIME NOT NULL,
    FOREIGN KEY (`definition_id`) REFERENCES `blackout_definitions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`room_id`) REFERENCES `room`(`id`) ON DELETE CASCADE,
    CHECK (`start_at` < `finish_at`)
); 