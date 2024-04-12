-- -----------------------------------------------------
-- Table `db_a993c8_freelan`.`tables_records_counts` should be affected by all the following triggers.
/*
 * Since we have a limited number of tables, then counting the records for each table using the InnoDB
 * and the COUNT(*) function could be non-efficient, even if there was a BTREE index on a column or a UNIQUE
 * BTREE index also, InnoDB still too bad, this following way (creating separate triggers) is very good even though the load is a
 * little higher in the overall insertion and deletion operations, but since the trigger is executed in about O(1) time complexity, then
 * it's a good way in our use case in the freelancing project "ProHub".
 */
-- -----------------------------------------------------

DELIMITER //

-- Create triggers for users table
CREATE TRIGGER `users_insert_trigger`
AFTER INSERT ON `users`
FOR EACH ROW
BEGIN
  -- Update the records count for the users table
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('users', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `users_delete_trigger`
AFTER DELETE ON `users`
FOR EACH ROW
BEGIN
  -- Update the records count for the users table
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('users', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for email_verification_codes table
CREATE TRIGGER `email_verification_codes_insert_trigger`
AFTER INSERT ON `email_verification_codes`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('email_verification_codes', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `email_verification_codes_delete_trigger`
AFTER DELETE ON `email_verification_codes`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('email_verification_codes', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for freelancer_pdfs table
CREATE TRIGGER `freelancer_pdfs_insert_trigger`
AFTER INSERT ON `freelancer_pdfs`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('freelancer_pdfs', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `freelancer_pdfs_delete_trigger`
AFTER DELETE ON `freelancer_pdfs`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('freelancer_pdfs', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for freelancer_tags table
CREATE TRIGGER `freelancer_tags_insert_trigger`
AFTER INSERT ON `freelancer_tags`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('freelancer_tags', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `freelancer_tags_delete_trigger`
AFTER DELETE ON `freelancer_tags`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('freelancer_tags', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for freelancers table
CREATE TRIGGER `freelancers_insert_trigger`
AFTER INSERT ON `freelancers`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('freelancers', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `freelancers_delete_trigger`
AFTER DELETE ON `freelancers`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('freelancers', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for incomplete_registration_users table
CREATE TRIGGER `incomplete_registration_users_insert_trigger`
AFTER INSERT ON `incomplete_registration_users`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('incomplete_registration_users', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `incomplete_registration_users_delete_trigger`
AFTER DELETE ON `incomplete_registration_users`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('incomplete_registration_users', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for job_tags table
CREATE TRIGGER `job_tags_insert_trigger`
AFTER INSERT ON `job_tags`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('job_tags', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `job_tags_delete_trigger`
AFTER DELETE ON `job_tags`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('job_tags', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for jobs table
CREATE TRIGGER `jobs_insert_trigger`
AFTER INSERT ON `jobs`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('jobs', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `jobs_delete_trigger`
AFTER DELETE ON `jobs`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('jobs', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for tags table
CREATE TRIGGER `tags_insert_trigger`
AFTER INSERT ON `tags`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('tags', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `tags_delete_trigger`
AFTER DELETE ON `tags`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('tags', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for tags_suggested_by_users table
CREATE TRIGGER `tags_suggested_by_users_insert_trigger`
AFTER INSERT ON `tags_suggested_by_users`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('tags_suggested_by_users', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `tags_suggested_by_users_delete_trigger`
AFTER DELETE ON `tags_suggested_by_users`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('tags_suggested_by_users', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for user_favorite_jobs table
CREATE TRIGGER `user_favorite_jobs_insert_trigger`
AFTER INSERT ON `user_favorite_jobs`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('user_favorite_jobs', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `user_favorite_jobs_delete_trigger`
AFTER DELETE ON `user_favorite_jobs`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('user_favorite_jobs', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


-- Create triggers for user_favorite_users table
CREATE TRIGGER `user_favorite_users_insert_trigger`
AFTER INSERT ON `user_favorite_users`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('user_favorite_users', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `user_favorite_users_delete_trigger`
AFTER DELETE ON `user_favorite_users`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('user_favorite_users', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//


/*
-- Create triggers for tables_records_counts table
CREATE TRIGGER `tables_records_counts_insert_trigger`
AFTER INSERT ON `tables_records_counts`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('tables_records_counts', 1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` + 1;
END//

CREATE TRIGGER `tables_records_counts_delete_trigger`
AFTER DELETE ON `tables_records_counts`
FOR EACH ROW
BEGIN
  INSERT INTO `tables_records_counts` (`table_name`, `records_count`)
  VALUES ('tables_records_counts', -1)
  ON DUPLICATE KEY UPDATE `records_count` = `records_count` - 1;
END//
*/

DELIMITER ;


/*
SELECT trigger_name 
FROM information_schema.triggers 
WHERE trigger_schema = 'freelancing project';
*/


/*
BEGIN;
-- Disable foreign key checks to avoid issues with dependencies
SET FOREIGN_KEY_CHECKS = 0;

-- Drop the triggers
DROP TRIGGER IF EXISTS email_verification_codes_insert_trigger;
DROP TRIGGER IF EXISTS users_insert_trigger;
DROP TRIGGER IF EXISTS users_delete_trigger;

-- Enable foreign key checks again
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
*/
