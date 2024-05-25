CREATE TABLE `todos` (
  `id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` TEXT NOT NULL,
  `date_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checked` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_task` (
  `user_id` INT(6) UNSIGNED NOT NULL,
  `task_id` INT(6) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `task_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`task_id`) REFERENCES `todos`(`id`)
);

CREATE TABLE `users` (
  `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(30) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `reg_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;