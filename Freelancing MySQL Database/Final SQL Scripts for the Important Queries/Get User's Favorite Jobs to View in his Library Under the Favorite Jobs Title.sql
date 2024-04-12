
/*
This query retrieves information about the user's favorite jobs, returning various details related to each job.

To retrieve the required data, only the user's ID is needed. Note that the user can mark their own posted jobs
as favorites, as well as jobs that have already been completed by a freelancer.

The results of the query are ordered based on the job creation date, with the most recent jobs appearing first.

I am satisfied with the current time complexity, so to remind myself in the future, I can run the EXPLAIN ANALYZE FORMAT = TREE query to
check that this query is the best query that could be written in terms of performance because it retrieves only the needed data without
scanning any other data from any record that is not involved in the final result set;).
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
  (
    SELECT 
      GROUP_CONCAT(
        DISTINCT `freelancing project`.`tags`.tag_name SEPARATOR ', '
      ) 
    FROM 
      `freelancing project`.`job_tags` 
      LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id 
    WHERE 
      `freelancing project`.`job_tags`.job_id = table1.job_id 
    GROUP BY 
      `freelancing project`.`job_tags`.job_id
  ) AS job_tags, 
  table1.freelancer_id, 
  table1.freelancer_first_name, 
  table1.freelancer_last_name, 
  table1.freelancer_rate, 
  table1.freelancer_profile_picture_path 
FROM 
  (
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
      helper_table_name.user_profile_picture_path AS freelancer_profile_picture_path 
    FROM 
      `freelancing project`.`jobs` 
      LEFT JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id 
      LEFT JOIN `freelancing project`.`users` AS helper_table_name ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.helper_table_name.user_id 
      LEFT JOIN `freelancing project`.`freelancers` ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.`freelancers`.freelancer_id 
    WHERE 
      `freelancing project`.`jobs`.job_id IN (
        SELECT 
          `freelancing project`.`user_favorite_jobs`.job_id 
        FROM 
          `freelancing project`.`user_favorite_jobs` 
        WHERE 
          `freelancing project`.`user_favorite_jobs`.user_id = 1
      ) 
    ORDER BY 
      `freelancing project`.`jobs`.job_creation_date DESC
  ) AS table1;
  
  