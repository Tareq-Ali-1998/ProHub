-- -----------------------------------------------------
-- Deletes old records from the `incomplete_registration_users` table
-- -----------------------------------------------------
CREATE EVENT IF NOT EXISTS `delete_old_records`
ON SCHEDULE EVERY 30 MINUTE
DO
  DELETE FROM `db_a993c8_freelan`.`incomplete_registration_users`
  WHERE (`db_a993c8_freelan`.`incomplete_registration_users`.user_registration_date < DATE_SUB(NOW(), INTERVAL 30 MINUTE) AND
         `db_a993c8_freelan`.`incomplete_registration_users`.user_verification_success = 0);
