
/*
This query retrieves the jobs information for the completed and non-completed jobs, and if the job is completed (have
been done by some freelancer), then it will retrieves the freelancer_id, the frist_name, the last_name, and the profile_picture_path
of the freelancer, and if the job hasn't been completed yet by any freelancer (it's an available job), then all those values will be null.

The results include various details related to each job and will be displayed as default jobs for non-freelancer
users in their job browsing list.

The result records of the query are ordered based on the job_creation_date columns in the jobs table, with the most recent jobs appearing first.

To remind myself in the future, I can run the EXPLAIN ANALYZE FORMAT = TREE query to check that this query is the
best query that could be written in terms of performance because it retrieves only the needed data without scanning any other data
from any record that is not involved in the final result set;).

*/

EXPLAIN ANALYZE FORMAT = TREE
SELECT 
    table1.job_id, 
    table1.publisher_id, 
    table1.publisher_first_name, 
    table1.publisher_last_name,
    table1.publisher_profile_picture_path,
    table1.job_title, 
    table1.job_description, 
    table1.job_creation_date,
    table1.job_deadline_date, 
    table1.table1.job_price,
    table1.table1.job_completion_date,
    table1.table1.job_completion_rating,
    table1.table1.job_completion_message,
    table1.is_favorite,
    (	
        SELECT GROUP_CONCAT(DISTINCT `freelancing project`.`tags`.tag_name SEPARATOR ', ')
        FROM `freelancing project`.`job_tags`
        LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id
        WHERE `freelancing project`.`job_tags`.job_id = table1.job_id
        GROUP BY `freelancing project`.`job_tags`.job_id
    ) AS job_tags,
    table1.freelancer_id,
    table1.freelancer_first_name,
    table1.freelancer_last_name,
    table1.freelancer_rate,
    table1.freelancer_profile_picture_path
FROM (
    SELECT
        `freelancing project`.`jobs`.job_id, 
        `freelancing project`.`users`.user_id AS publisher_id, 
        `freelancing project`.`users`.user_first_name AS publisher_first_name, 
        `freelancing project`.`users`.user_last_name AS publisher_last_name,
        `freelancing project`.`users`.user_profile_picture_path AS publisher_profile_picture_path, 
        `freelancing project`.`jobs`.job_title, 
        `freelancing project`.`jobs`.job_description, 
        `freelancing project`.`jobs`.job_creation_date,
        `freelancing project`.`jobs`.job_deadline_date, 
        `freelancing project`.`jobs`.job_price,
        `freelancing project`.`jobs`.freelancer_id,
        `freelancing project`.`jobs`.job_completion_date,
        `freelancing project`.`jobs`.job_completion_rating,
        `freelancing project`.`jobs`.job_completion_message,
        `freelancers`.freelancer_rate,
        helper_table_name.user_first_name AS freelancer_first_name,
        helper_table_name.user_last_name AS freelancer_last_name,
        helper_table_name.user_profile_picture_path AS freelancer_profile_picture_path,
        CASE 
        WHEN `freelancing project`.`user_favorite_jobs`.user_id IS NULL 
        THEN FALSE
        ELSE TRUE 
		END AS is_favorite
    FROM `freelancing project`.`jobs`
    LEFT JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id
    LEFT JOIN `freelancing project`.`users` AS helper_table_name ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.helper_table_name.user_id
    LEFT JOIN `freelancing project`.`freelancers` ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.`freelancers`.freelancer_id
    LEFT JOIN `freelancing project`.`user_favorite_jobs` ON (`freelancing project`.`jobs`.job_id = `freelancing project`.`user_favorite_jobs`.job_id AND 
															 `freelancing project`.`user_favorite_jobs`.user_id = 10101)
    WHERE `freelancing project`.`jobs`.job_creation_date > '1190-04-21 05:27:34'
    ORDER BY `freelancing project`.`jobs`.job_creation_date DESC
    LIMIT 5
) AS table1;

/*

EXPLAIN ANALYZE FORMAT = TREE
SELECT 
    table1.job_id, 
    table1.publisher_id, 
    table1.publisher_first_name, 
    table1.publisher_last_name,
    table1.publisher_profile_picture_path,
    table1.job_title, 
    table1.job_description, 
    table1.job_creation_date,
    table1.job_deadline_date, 
    table1.table1.job_price,
    table1.table1.job_completion_date,
    table1.table1.job_completion_rating,
    table1.table1.job_completion_message,
    table1.is_favorite,
    (	
        SELECT GROUP_CONCAT(DISTINCT `freelancing project`.`tags`.tag_name SEPARATOR ', ')
        FROM `freelancing project`.`job_tags`
        LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id
        WHERE `freelancing project`.`job_tags`.job_id = table1.job_id
        GROUP BY `freelancing project`.`job_tags`.job_id
    ) AS tag_names,
    table1.freelancer_id,
    table1.freelancer_first_name,
    table1.freelancer_last_name,
    table1.freelancer_rate,
    table1.freelancer_profile_picture_path
FROM (
    SELECT
        `freelancing project`.`jobs`.job_id, 
        `freelancing project`.`users`.user_id AS publisher_id, 
        `freelancing project`.`users`.user_first_name AS publisher_first_name, 
        `freelancing project`.`users`.user_last_name AS publisher_last_name,
        `freelancing project`.`users`.user_profile_picture_path AS publisher_profile_picture_path, 
        `freelancing project`.`jobs`.job_title, 
        `freelancing project`.`jobs`.job_description, 
        `freelancing project`.`jobs`.job_creation_date,
        `freelancing project`.`jobs`.job_deadline_date, 
        `freelancing project`.`jobs`.job_price,
        `freelancing project`.`jobs`.freelancer_id,
        `freelancing project`.`jobs`.job_completion_date,
        `freelancing project`.`jobs`.job_completion_rating,
        `freelancing project`.`jobs`.job_completion_message,
        `freelancers`.freelancer_rate,
        helper_table_name.user_first_name AS freelancer_first_name,
        helper_table_name.user_last_name AS freelancer_last_name,
        helper_table_name.user_profile_picture_path AS freelancer_profile_picture_path,
        CASE 
        WHEN `freelancing project`.`user_favorite_jobs`.user_id IS NULL 
        THEN FALSE
        ELSE TRUE 
		END AS is_favorite
    FROM `freelancing project`.`jobs`
    LEFT JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id
    LEFT JOIN `freelancing project`.`users` AS helper_table_name ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.helper_table_name.user_id
    LEFT JOIN `freelancing project`.`freelancers` ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.`freelancers`.freelancer_id
    LEFT JOIN `freelancing project`.`user_favorite_jobs` ON (`freelancing project`.`jobs`.job_id = `freelancing project`.`user_favorite_jobs`.job_id AND 
															 `freelancing project`.`user_favorite_jobs`.user_id = 10101)
    ORDER BY `freelancing project`.`jobs`.job_creation_date DESC
    LIMIT 5 OFFSET 5
) AS table1;

*/
  
  
