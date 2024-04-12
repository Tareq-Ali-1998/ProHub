-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema db_a993c8_freelan
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema db_a993c8_freelan
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `db_a993c8_freelan` DEFAULT CHARACTER SET utf8 ;
USE `db_a993c8_freelan` ;

-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `user_first_name` VARCHAR(45) NOT NULL,
  `user_last_name` VARCHAR(45) NOT NULL,
  `user_email` VARCHAR(120) NOT NULL,
  `user_email_visibility` TINYINT NOT NULL DEFAULT 1,
  `user_phone_number` VARCHAR(20) NOT NULL,
  `user_phone_number_visibility` TINYINT NOT NULL DEFAULT 1,
  `user_password` VARCHAR(200) NOT NULL,
  `user_date_of_birth` DATE NOT NULL,
  `user_gender` VARCHAR(1) NOT NULL,
  `user_account_creation_date` DATETIME NOT NULL,
  `user_city` VARCHAR(30) NOT NULL,
  `user_specific_address` VARCHAR(200) NULL,
  `user_profile_picture_path` VARCHAR(200) NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_email UNIQUE INDEX` (`user_email` ASC) VISIBLE,
  UNIQUE INDEX `user_phone_number UNIQUE INDEX` (`user_phone_number` ASC) VISIBLE,
  FULLTEXT INDEX `COMPOSSED_FULLTEXT INDEX on user_first_name and user_last_name in order` (`user_first_name`, `user_last_name`) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`freelancers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`freelancers` (
  `freelancer_id` INT NOT NULL,
  `freelancer_description` VARCHAR(4000) NOT NULL,
  `freelancer_rate` INT NULL,
  `freelancer_hourly_rate` INT NULL,
  `freelancer_brief_description` VARCHAR(150) NULL,
  PRIMARY KEY (`freelancer_id`),
  INDEX `freelancer_rate INDEX` (`freelancer_rate` ASC) INVISIBLE,
  INDEX `freelancer_hourly_rate INDEX` (`freelancer_hourly_rate` ASC) VISIBLE,
  CONSTRAINT `fk_freelancers_users`
    FOREIGN KEY (`freelancer_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`tags` (
  `tag_id` INT NOT NULL AUTO_INCREMENT,
  `tag_name` VARCHAR(100) NOT NULL,
  `tag_name_ngram` VARCHAR(100) NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE INDEX `tag_name UNIQUE INDEX` (`tag_name` ASC) VISIBLE,
  FULLTEXT INDEX `tag_name FULLTEXT INDEX` (`tag_name`) VISIBLE,
  FULLTEXT INDEX `tag_name NGRAM_FULLTEXT INDEX` (`tag_name_ngram`) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`freelancer_tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`freelancer_tags` (
  `freelancer_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  PRIMARY KEY (`freelancer_id`, `tag_id`),
  INDEX `tag_id INDEX` (`tag_id` ASC) VISIBLE,
  CONSTRAINT `fk_freelancer_tags_freelancers1`
    FOREIGN KEY (`freelancer_id`)
    REFERENCES `db_a993c8_freelan`.`freelancers` (`freelancer_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_freelancer_tags_tags1`
    FOREIGN KEY (`tag_id`)
    REFERENCES `db_a993c8_freelan`.`tags` (`tag_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`freelancer_pdfs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`freelancer_pdfs` (
  `freelancer_id` INT NOT NULL,
  `pdf_path` VARCHAR(200) NOT NULL,
  `pdf_order_number` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`freelancer_id`, `pdf_path`),
  CONSTRAINT `fk_freelancer_pdfs_freelancers1`
    FOREIGN KEY (`freelancer_id`)
    REFERENCES `db_a993c8_freelan`.`freelancers` (`freelancer_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`jobs` (
  `job_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `job_title` VARCHAR(250) NOT NULL,
  `job_description` VARCHAR(4000) NOT NULL,
  `job_creation_date` DATETIME NOT NULL,
  `job_deadline_date` DATETIME NOT NULL,
  `job_price` INT NOT NULL,
  `freelancer_id` INT NULL,
  `job_completion_date` DATETIME NULL,
  `job_completion_rating` INT NULL,
  `job_completion_message` VARCHAR(1000) NULL,
  PRIMARY KEY (`job_id`),
  INDEX `FK INDEX user_id` (`user_id` ASC) INVISIBLE,
  INDEX `FK INDEX on freelancer_id` (`freelancer_id` ASC) VISIBLE,
  INDEX `job_creation_date INDEX` (`job_creation_date` ASC) VISIBLE,
  FULLTEXT INDEX `COMPOSED_FULLTEXT INDEX on job_title and job_description columns in order` (`job_title`, `job_description`) VISIBLE,
  CONSTRAINT `fk_jobs_users2`
    FOREIGN KEY (`user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_jobs_freelancers1`
    FOREIGN KEY (`freelancer_id`)
    REFERENCES `db_a993c8_freelan`.`freelancers` (`freelancer_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`job_tags`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`job_tags` (
  `job_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  PRIMARY KEY (`job_id`, `tag_id`),
  INDEX `tag_id INDEX` (`tag_id` ASC) VISIBLE,
  CONSTRAINT `fk_job_tags_jobs1`
    FOREIGN KEY (`job_id`)
    REFERENCES `db_a993c8_freelan`.`jobs` (`job_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_job_tags_tags1`
    FOREIGN KEY (`tag_id`)
    REFERENCES `db_a993c8_freelan`.`tags` (`tag_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`tags_suggested_by_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`tags_suggested_by_users` (
  `user_id` INT NOT NULL,
  `tag_name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`user_id`, `tag_name`),
  CONSTRAINT `fk_tags_suggested_by_users_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`user_favorite_jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`user_favorite_jobs` (
  `user_id` INT NOT NULL,
  `job_id` INT NOT NULL,
  `favorite_job_adding_date` DATETIME NOT NULL DEFAULT NOW(),
  PRIMARY KEY (`user_id`, `job_id`),
  INDEX `job_id INDEX` (`job_id` ASC) VISIBLE,
  INDEX `COMPOSSED_INDEX on user_id and favorite_job_adding_date columns in order` (`user_id` ASC, `favorite_job_adding_date` ASC) VISIBLE,
  CONSTRAINT `fk_user_favorite_jobs_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_favorite_jobs_jobs1`
    FOREIGN KEY (`job_id`)
    REFERENCES `db_a993c8_freelan`.`jobs` (`job_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`incomplete_registration_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`incomplete_registration_users` (
  `user_email` VARCHAR(120) NOT NULL,
  `user_first_name` VARCHAR(45) NOT NULL,
  `user_last_name` VARCHAR(45) NOT NULL,
  `user_password` VARCHAR(200) NOT NULL,
  `user_verification_code` VARCHAR(9) NOT NULL,
  `user_registration_date` DATETIME NOT NULL DEFAULT NOW(),
  `user_registration_status` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_email`),
  INDEX `user_registration_date INDEX` (`user_registration_date` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`email_verification_codes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`email_verification_codes` (
  `verification_code` VARCHAR(9) NOT NULL,
  PRIMARY KEY (`verification_code`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`tables_records_counts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`tables_records_counts` (
  `table_name` VARCHAR(100) NOT NULL,
  `records_count` INT NULL DEFAULT 0,
  PRIMARY KEY (`table_name`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`chatgpt_api_messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`chatgpt_api_messages` (
  `message_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `message_date` DATETIME NOT NULL DEFAULT NOW(),
  `request_content` VARCHAR(5000) NULL,
  `response_content` VARCHAR(5000) NULL,
  `for_job_categorization` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`message_id`),
  INDEX `COMPOSED_INDEX on user_id and message_date columns in order` (`user_id` ASC, `message_date` ASC) VISIBLE,
  INDEX `message_date INDEX` (`message_date` ASC) VISIBLE,
  CONSTRAINT `fk_chatgpt_api_messages_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`user_favorite_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`user_favorite_users` (
  `user_id` INT NOT NULL,
  `favorite_user_id` INT NOT NULL,
  `favorite_user_adding_date` DATETIME NOT NULL,
  PRIMARY KEY (`user_id`, `favorite_user_id`),
  INDEX `FK INDEX on favorite_user_id` (`favorite_user_id` ASC) VISIBLE,
  INDEX `COMPOSSED_INDEX on user_id and favorite_user_adding_date columns in order` (`user_id` ASC, `favorite_user_adding_date` ASC) VISIBLE,
  CONSTRAINT `fk_user_favorite_users_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_favorite_users_users2`
    FOREIGN KEY (`favorite_user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`recommendation_notifications_queue`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_a993c8_freelan`.`recommendation_notifications_queue` (
  `user_id` INT NOT NULL,
  `job_id` INT NOT NULL,
  PRIMARY KEY (`job_id`, `user_id`),
  CONSTRAINT `fk_recommendation_notifications_queue_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `db_a993c8_freelan`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_recommendation_notifications_queue_jobs1`
    FOREIGN KEY (`job_id`)
    REFERENCES `db_a993c8_freelan`.`jobs` (`job_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
