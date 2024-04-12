
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
    table1.job_price,
    table1.freelancer_id,
    tag_names
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
        `freelancing project`.`jobs`.freelancer_id
    FROM `freelancing project`.`jobs`
    LEFT JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id
    ORDER BY `freelancing project`.`jobs`.job_creation_date DESC
    LIMIT 20 OFFSET 300
) AS table1
LEFT JOIN (
    SELECT 
        `freelancing project`.`job_tags`.job_id,
        GROUP_CONCAT(DISTINCT `freelancing project`.`tags`.tag_name SEPARATOR ', ') AS tag_names
    FROM `freelancing project`.`job_tags`
    LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id
    GROUP BY `freelancing project`.`job_tags`.job_id
) AS table2
ON table1.job_id = table2.job_id
*/

/*

'-> Nested loop left join  (cost=2027.39 rows=560) (actual time=6.476..6.512 rows=20 loops=1)
    -> Table scan on table1  (cost=0.14..2.75 rows=20) (actual time=6.274..6.285 rows=20 loops=1)
        -> Materialize  (cost=1744.78..1747.39 rows=20) (actual time=6.268..6.268 rows=20 loops=1)
            -> Limit/Offset: 20/300 row(s)  (cost=1742.64 rows=20) (actual time=6.043..6.182 rows=20 loops=1)
                -> Nested loop left join  (cost=1742.64 rows=320) (actual time=0.094..6.030 rows=320 loops=1)
                    -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.64 rows=320) (actual time=0.079..3.676 rows=320 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.007..0.007 rows=1 loops=320)
    -> Index lookup on table2 using <auto_key0> (job_id=table1.job_id)  (actual time=0.011..0.011 rows=0 loops=20)
        -> Materialize  (cost=18.45..18.45 rows=28) (actual time=0.194..0.194 rows=4 loops=1)
            -> Group aggregate: group_concat(distinct tags.tag_name separator '', '')  (cost=15.65 rows=28) (actual time=0.072..0.158 rows=4 loops=1)
                -> Nested loop left join  (cost=12.85 rows=28) (actual time=0.029..0.118 rows=28 loops=1)
                    -> Covering index scan on job_tags using PRIMARY  (cost=3.05 rows=28) (actual time=0.019..0.036 rows=28 loops=1)
                    -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.002..0.003 rows=1 loops=28)
'

'-> Nested loop left join  (cost=1957.39 rows=420) (actual time=3.292..3.345 rows=20 loops=1)
    -> Table scan on table1  (cost=0.14..2.75 rows=20) (actual time=3.013..3.028 rows=20 loops=1)
        -> Materialize  (cost=1744.78..1747.39 rows=20) (actual time=3.007..3.007 rows=20 loops=1)
            -> Limit/Offset: 20/300 row(s)  (cost=1742.64 rows=20) (actual time=2.673..2.898 rows=20 loops=1)
                -> Nested loop left join  (cost=1742.64 rows=320) (actual time=0.084..2.861 rows=320 loops=1)
                    -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.64 rows=320) (actual time=0.069..1.534 rows=320 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=320)
    -> Index lookup on table2 using <auto_key0> (job_id=table1.job_id)  (actual time=0.015..0.015 rows=0 loops=20)
        -> Materialize  (cost=13.90..13.90 rows=21) (actual time=0.271..0.271 rows=3 loops=1)
            -> Group aggregate: group_concat(distinct tags.tag_name separator '', '')  (cost=11.80 rows=21) (actual time=0.115..0.227 rows=3 loops=1)
                -> Nested loop left join  (cost=9.70 rows=21) (actual time=0.031..0.159 rows=21 loops=1)
                    -> Covering index scan on job_tags using PRIMARY  (cost=2.35 rows=21) (actual time=0.021..0.054 rows=21 loops=1)
                    -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=21)
'




'-> Nested loop left join  (cost=1935.39 rows=280) (actual time=2.916..2.954 rows=20 loops=1)
    -> Table scan on table1  (cost=0.14..2.75 rows=20) (actual time=2.784..2.796 rows=20 loops=1)
        -> Materialize  (cost=1744.78..1747.39 rows=20) (actual time=2.778..2.778 rows=20 loops=1)
            -> Limit/Offset: 20/300 row(s)  (cost=1742.64 rows=20) (actual time=2.544..2.700 rows=20 loops=1)
                -> Nested loop left join  (cost=1742.64 rows=320) (actual time=0.086..2.629 rows=320 loops=1)
                    -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.64 rows=320) (actual time=0.072..1.482 rows=320 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=1 loops=320)
    -> Index lookup on table2 using <auto_key0> (job_id=table1.job_id)  (actual time=0.007..0.007 rows=0 loops=20)
        -> Materialize  (cost=9.35..9.35 rows=14) (actual time=0.127..0.127 rows=2 loops=1)
            -> Group aggregate: group_concat(distinct tags.tag_name separator '', '')  (cost=7.95 rows=14) (actual time=0.070..0.098 rows=2 loops=1)
                -> Nested loop left join  (cost=6.55 rows=14) (actual time=0.024..0.071 rows=14 loops=1)
                    -> Covering index scan on job_tags using PRIMARY  (cost=1.65 rows=14) (actual time=0.015..0.024 rows=14 loops=1)
                    -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.26 rows=1) (actual time=0.003..0.003 rows=1 loops=14)
'



*/