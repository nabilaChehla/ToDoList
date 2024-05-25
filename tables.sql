CREATE TABLE `TODOS` (
  `ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `TITLE` TEXT NOT NULL,
  `DATE_TIME` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CHECKED` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=INNODB DEFAULT CHARSET=UTF8MB4 COLLATE=UTF8MB4_UNICODE_CI;

CREATE TABLE `USER_TASK` (
  `USER_ID` INT(6) UNSIGNED NOT NULL,
  `TASK_ID` INT(6) UNSIGNED NOT NULL,
  PRIMARY KEY (`USER_ID`, `TASK_ID`),
  FOREIGN KEY (`USER_ID`) REFERENCES `USERS`(`ID`),
  FOREIGN KEY (`TASK_ID`) REFERENCES `TODOS`(`ID`)
);

CREATE TABLE `USERS` (
  `ID` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `USERNAME` VARCHAR(30) NOT NULL,
  `PASSWORD` VARCHAR(32) NOT NULL,
  `REG_DATE` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=INNODB DEFAULT CHARSET=UTF8MB4 COLLATE=UTF8MB4_UNICODE_CI;

CREATE TABLE `PROJECT` (
  `ID` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `PROJECT_NAME` VARCHAR(30) NOT NULL,
  `MANAGER_ID` INT(6) UNSIGNED NOT NULL,
  FOREIGN KEY (`MANAGER_ID`) REFERENCES `USERS`(`ID`)
) ENGINE=INNODB DEFAULT CHARSET=UTF8MB4 COLLATE=UTF8MB4_UNICODE_CI;


CREATE TABLE `PROJECT_USER_TASK` (
  `PROJECT_ID` INT(6) UNSIGNED NOT NULL,
  `USER_ID` INT(6) UNSIGNED NOT NULL,
  `TASK_ID` INT(6) UNSIGNED NOT NULL ,
  PRIMARY KEY (`PROJECT_ID`,`USER_ID`, `TASK_ID`),
  FOREIGN KEY (`PROJECT_ID`) REFERENCES `PROJECT`(`ID`),
  FOREIGN KEY (`USER_ID`) REFERENCES `USERS`(`ID`),
  FOREIGN KEY (`TASK_ID`) REFERENCES `TODOS`(`ID`)
) ENGINE=INNODB DEFAULT CHARSET=UTF8MB4 COLLATE=UTF8MB4_UNICODE_CI;