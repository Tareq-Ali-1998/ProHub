

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
        helper_table_name.user_profile_picture_path AS freelancer_profile_picture_path
    FROM `freelancing project`.`jobs`
    LEFT JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id
    LEFT JOIN `freelancing project`.`users` AS helper_table_name ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.helper_table_name.user_id
    LEFT JOIN `freelancing project`.`freelancers` ON `freelancing project`.`jobs`.freelancer_id = `freelancing project`.`freelancers`.freelancer_id
    ORDER BY `freelancing project`.`jobs`.job_creation_date DESC
    LIMIT 5 OFFSET 5
) AS table1;

-- Lefting join the table twice.

-- UPDATE jobs SET freelancer_id = 1  WHERE job_id = 7557;

/*

'-> Table scan on table1  (cost=0.51..2.56 rows=5) (actual time=0.396..0.402 rows=5 loops=1)
    -> Materialize  (cost=1408.90..1410.95 rows=5) (actual time=0.392..0.392 rows=5 loops=1)
        -> Limit/Offset: 5/5 row(s)  (cost=1407.88 rows=5) (actual time=0.245..0.346 rows=5 loops=1)
            -> Nested loop left join  (cost=1407.88 rows=10) (actual time=0.197..0.342 rows=10 loops=1)
                -> Nested loop left join  (cost=938.63 rows=10) (actual time=0.193..0.330 rows=10 loops=1)
                    -> Nested loop left join  (cost=469.38 rows=10) (actual time=0.189..0.318 rows=10 loops=1)
                        -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=0.13 rows=10) (actual time=0.161..0.237 rows=10 loops=1)
                        -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.007..0.007 rows=1 loops=10)
                    -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=10)
                -> Single-row index lookup on freelancers using PRIMARY (freelancer_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=10)
'


ALTER TABLE jobs 
ADD COLUMN job_completion_message VARCHAR(500) DEFAULT NULL;

'-> Table scan on table1  (cost=0.02..8.75 rows=500) (actual time=18.067..18.504 rows=500 loops=1)
    -> Materialize  (cost=1565.47..1574.20 rows=500) (actual time=18.060..18.060 rows=500 loops=1)
        -> Limit/Offset: 500/5 row(s)  (cost=1515.45 rows=500) (actual time=11.972..15.614 rows=500 loops=1)
            -> Nested loop left join  (cost=1515.45 rows=1864) (actual time=11.933..15.508 rows=505 loops=1)
                -> Nested loop left join  (cost=863.05 rows=1864) (actual time=11.926..15.064 rows=505 loops=1)
                    -> Sort: jobs.job_creation_date DESC, limit input to 505 row(s) per chunk  (cost=210.65 rows=1864) (actual time=11.888..12.393 rows=505 loops=1)
                        -> Table scan on jobs  (cost=210.65 rows=1864) (actual time=0.049..4.338 rows=1892 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.005..0.005 rows=1 loops=505)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=505)
'


'-> Table scan on table1  (cost=0.06..3.12 rows=50) (actual time=1.290..1.336 rows=50 loops=1)
    -> Materialize  (cost=948.94..952.00 rows=50) (actual time=1.285..1.285 rows=50 loops=1)
        -> Limit/Offset: 50/5 row(s)  (cost=943.88 rows=50) (actual time=0.171..1.013 rows=50 loops=1)
            -> Nested loop left join  (cost=943.88 rows=55) (actual time=0.132..1.001 rows=55 loops=1)
                -> Nested loop left join  (cost=472.38 rows=55) (actual time=0.128..0.948 rows=55 loops=1)
                    -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=0.88 rows=55) (actual time=0.112..0.653 rows=55 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.005..0.005 rows=1 loops=55)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=55)
'

'-> Table scan on table1  (cost=0.51..2.56 rows=5) (actual time=0.235..0.238 rows=5 loops=1)
    -> Materialize  (cost=935.15..937.20 rows=5) (actual time=0.231..0.231 rows=5 loops=1)
        -> Limit/Offset: 5/5 row(s)  (cost=934.14 rows=5) (actual time=0.144..0.201 rows=5 loops=1)
            -> Nested loop left join  (cost=934.14 rows=10) (actual time=0.110..0.198 rows=10 loops=1)
                -> Nested loop left join  (cost=467.14 rows=10) (actual time=0.106..0.187 rows=10 loops=1)
                    -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=0.14 rows=10) (actual time=0.089..0.133 rows=10 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.005..0.005 rows=1 loops=10)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=10)
'

'-> Table scan on table1  (cost=0.51..2.56 rows=5) (actual time=6.954..6.959 rows=5 loops=1)
    -> Materialize  (cost=1517.91..1519.96 rows=5) (actual time=6.949..6.949 rows=5 loops=1)
        -> Limit/Offset: 5/5 row(s)  (cost=1516.90 rows=5) (actual time=6.879..6.908 rows=5 loops=1)
            -> Nested loop left join  (cost=1516.90 rows=1893) (actual time=6.843..6.905 rows=10 loops=1)
                -> Nested loop left join  (cost=854.35 rows=1893) (actual time=6.836..6.891 rows=10 loops=1)
                    -> Sort: jobs.job_creation_date DESC, limit input to 10 row(s) per chunk  (cost=191.80 rows=1893) (actual time=6.808..6.813 rows=10 loops=1)
                        -> Table scan on jobs  (cost=191.80 rows=1893) (actual time=0.050..4.381 rows=1892 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.007..0.007 rows=1 loops=10)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=10)
'
'-> Table scan on table1  (cost=0.51..2.56 rows=5) (actual time=0.775..0.780 rows=5 loops=1)
    -> Materialize  (cost=141.11..143.16 rows=5) (actual time=0.771..0.771 rows=5 loops=1)
        -> Limit/Offset: 5/5 row(s)  (cost=140.10 rows=5) (actual time=0.703..0.731 rows=5 loops=1)
            -> Nested loop left join  (cost=140.10 rows=172) (actual time=0.665..0.728 rows=10 loops=1)
                -> Nested loop left join  (cost=79.90 rows=172) (actual time=0.661..0.715 rows=10 loops=1)
                    -> Sort: jobs.job_creation_date DESC, limit input to 10 row(s) per chunk  (cost=19.70 rows=172) (actual time=0.641..0.647 rows=10 loops=1)
                        -> Table scan on jobs  (cost=19.70 rows=172) (actual time=0.052..0.420 rows=172 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.006..0.006 rows=1 loops=10)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=10)
'
'-> Table scan on table1  (cost=0.14..2.75 rows=20) (actual time=0.706..0.719 rows=20 loops=1)
    -> Materialize  (cost=142.24..144.85 rows=20) (actual time=0.703..0.703 rows=20 loops=1)
        -> Limit/Offset: 20/5 row(s)  (cost=140.10 rows=20) (actual time=0.520..0.620 rows=20 loops=1)
            -> Nested loop left join  (cost=140.10 rows=172) (actual time=0.495..0.615 rows=25 loops=1)
                -> Nested loop left join  (cost=79.90 rows=172) (actual time=0.490..0.591 rows=25 loops=1)
                    -> Sort: jobs.job_creation_date DESC, limit input to 25 row(s) per chunk  (cost=19.70 rows=172) (actual time=0.472..0.483 rows=25 loops=1)
                        -> Table scan on jobs  (cost=19.70 rows=172) (actual time=0.045..0.318 rows=172 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=25)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=25)
'

'-> Table scan on table1  (cost=0.14..2.75 rows=20) (actual time=0.902..0.919 rows=20 loops=1)
    -> Materialize  (cost=142.24..144.85 rows=20) (actual time=0.897..0.897 rows=20 loops=1)
        -> Limit/Offset: 20/20 row(s)  (cost=140.10 rows=20) (actual time=0.678..0.798 rows=20 loops=1)
            -> Nested loop left join  (cost=140.10 rows=172) (actual time=0.550..0.791 rows=40 loops=1)
                -> Nested loop left join  (cost=79.90 rows=172) (actual time=0.546..0.751 rows=40 loops=1)
                    -> Sort: jobs.job_creation_date DESC, limit input to 40 row(s) per chunk  (cost=19.70 rows=172) (actual time=0.526..0.547 rows=40 loops=1)
                        -> Table scan on jobs  (cost=19.70 rows=172) (actual time=0.041..0.374 rows=172 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.005 rows=1 loops=40)
                -> Single-row index lookup on helper_table_name using PRIMARY (user_id=jobs.freelancer_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=40)
'
*/
  
  
