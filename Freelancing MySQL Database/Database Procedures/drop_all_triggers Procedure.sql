--WARNING! ERRORS ENCOUNTERED DURING SQL PARSING!
CREATE DEFINER = `root`@`localhost` PROCEDURE `drop_all_triggers` ()

BEGIN
	DECLARE done INT DEFAULT FALSE;
	DECLARE trigger_name VARCHAR(300);

	DECLARE cur CURSOR
	FOR
	SELECT trigger_name
	FROM information_schema.triggers
	WHERE trigger_schema = DATABASE ();

	DECLARE

	CONTINUE HANDLER
	FOR NOT FOUND

	SET done = TRUE;

	OPEN cur;

	read_loop: LOOP

	FETCH cur
	INTO trigger_name;

	IF done THEN LEAVE read_loop;END
		IF ;
			SET @stmt = CONCAT (
					'DROP TRIGGER IF EXISTS '
					,trigger_name
					);

	PREPARE stmt
	FROM @stmt;

	EXECUTE stmt;

	DEALLOCATE PREPARE stmt;
END

LOOP;

CLOSE cur;END
