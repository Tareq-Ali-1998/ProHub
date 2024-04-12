




SELECT MAX(user_account_creation_date) FROM users;





















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
    `freelancing project`.`tags`.tag_name
    -- GROUP_CONCAT(DISTINCT `freelancing project`.`tags`.tag_name SEPARATOR ', ') AS tag_names
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
LEFT JOIN `freelancing project`.`job_tags` ON table1.job_id = `freelancing project`.`job_tags`.job_id
LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id
GROUP BY table1.job_id





/*

'-> Limit/Offset: 20/300 row(s)  (cost=1742.64 rows=20) (actual time=3.383..3.569 rows=20 loops=1)
    -> Nested loop left join  (cost=1742.64 rows=320) (actual time=0.164..3.521 rows=320 loops=1)
        -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.64 rows=320) (actual time=0.097..1.807 rows=320 loops=1)
        -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.005..0.005 rows=1 loops=320)
'

'-> Group aggregate: group_concat(distinct tags.tag_name separator '', '')  (cost=1910.64 rows=20) (actual time=2.651..2.864 rows=20 loops=1)
    -> Nested loop left join  (cost=1908.64 rows=20) (actual time=2.635..2.785 rows=32 loops=1)
        -> Nested loop left join  (cost=1826.64 rows=20) (actual time=2.631..2.726 rows=32 loops=1)
            -> Sort: table1.job_id  (cost=1744.64..1744.64 rows=20) (actual time=2.618..2.627 rows=20 loops=1)
                -> Table scan on table1  (cost=38.50 rows=320) (actual time=2.561..2.573 rows=20 loops=1)
                    -> Materialize  (cost=1744.64..1744.64 rows=20) (actual time=2.555..2.555 rows=20 loops=1)
                        -> Limit/Offset: 20/300 row(s)  (cost=1742.64 rows=20) (actual time=2.320..2.479 rows=20 loops=1)
                            -> Nested loop left join  (cost=1742.64 rows=320) (actual time=0.081..2.444 rows=320 loops=1)
                                -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.64 rows=320) (actual time=0.067..1.351 rows=320 loops=1)
                                -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=1 loops=320)
            -> Covering index lookup on job_tags using PRIMARY (job_id=table1.job_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=20)
        -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.001..0.001 rows=0 loops=32)
'

-- GROUP_CONCAT(`freelancing project`.`tags`.tag_name) AS job_tags

'-> Limit/Offset: 10/300 row(s)  (actual time=131.080..131.086 rows=10 loops=1)
    -> Sort: jobs.job_creation_date DESC, limit input to 310 row(s) per chunk  (actual time=130.854..131.060 rows=310 loops=1)
        -> Stream results  (cost=8613.25 rows=6820) (actual time=0.105..112.319 rows=7396 loops=1)
            -> Group (no aggregates)  (cost=8613.25 rows=6820) (actual time=0.098..91.676 rows=7396 loops=1)
                -> Nested loop left join  (cost=7931.25 rows=6820) (actual time=0.080..78.915 rows=7408 loops=1)
                    -> Nested loop left join  (cost=5544.25 rows=6820) (actual time=0.077..73.607 rows=7408 loops=1)
                        -> Nested loop left join  (cost=3157.25 rows=6820) (actual time=0.064..46.720 rows=7396 loops=1)
                            -> Index scan on jobs using PRIMARY  (cost=770.25 rows=6820) (actual time=0.045..14.647 rows=7396 loops=1)
                            -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=7396)
                        -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=0 loops=7396)
                    -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=7408)
'


INSERT INTO job_tags VALUES (212, 10), (212, 202), (212, 310), (212, 300), (212, 20), (212, 30), (212, 50);
INSERT INTO job_tags VALUES (3171, 10), (3171, 202), (3171, 310), (3171, 300), (3171, 20), (3171, 30), (3171, 50);
INSERT INTO job_tags VALUES (1, 10), (1, 202), (1, 310), (1, 300), (1, 20), (1, 30), (1, 50);
INSERT INTO job_tags VALUES (2, 10), (2, 202), (2, 310), (2, 300), (2, 20), (2, 30), (2, 50);
INSERT INTO job_tags VALUES (3, 110), (3, 222), (3, 102), (6, 300), (6, 20), (6, 33), (7, 51);


'-> Limit/Offset: 10/300 row(s)  (cost=5213.42 rows=10) (actual time=4.003..4.116 rows=10 loops=1)
    -> Nested loop left join  (cost=5213.42 rows=310) (actual time=0.121..4.079 rows=310 loops=1)
        -> Nested loop left join  (cost=3477.42 rows=310) (actual time=0.118..3.822 rows=310 loops=1)
            -> Nested loop left join  (cost=1741.42 rows=310) (actual time=0.109..3.020 rows=310 loops=1)
                -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.42 rows=310) (actual time=0.094..1.648 rows=310 loops=1)
                -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=310)
            -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=310)
        -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=310)
'

'-> Limit/Offset: 10/300 row(s)  (cost=5213.42 rows=10) (actual time=5.924..6.081 rows=10 loops=1)
    -> Nested loop left join  (cost=5213.42 rows=310) (actual time=0.150..6.048 rows=310 loops=1)
        -> Nested loop left join  (cost=3477.42 rows=310) (actual time=0.147..5.718 rows=310 loops=1)
            -> Nested loop left join  (cost=1741.42 rows=310) (actual time=0.135..4.819 rows=310 loops=1)
                -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.42 rows=310) (actual time=0.116..2.754 rows=310 loops=1)
                -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.006..0.006 rows=1 loops=310)
            -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=310)
        -> Single-row covering index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=310)
'

'-> Limit/Offset: 10/300 row(s)  (cost=5213.42 rows=10) (actual time=4.516..4.624 rows=10 loops=1)
    -> Nested loop left join  (cost=5213.42 rows=310) (actual time=0.122..4.579 rows=310 loops=1)
        -> Nested loop left join  (cost=3477.42 rows=310) (actual time=0.119..4.293 rows=310 loops=1)
            -> Nested loop left join  (cost=1741.42 rows=310) (actual time=0.110..3.344 rows=310 loops=1)
                -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.42 rows=310) (actual time=0.092..1.774 rows=310 loops=1)
                -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=310)
            -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=310)
        -> Single-row covering index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=310)
'


'-> Limit/Offset: 10/300 row(s)  (cost=5213.42 rows=10) (actual time=3.631..3.748 rows=10 loops=1)
    -> Nested loop left join  (cost=5213.42 rows=310) (actual time=0.163..3.717 rows=310 loops=1)
        -> Nested loop left join  (cost=3477.42 rows=310) (actual time=0.160..3.500 rows=310 loops=1)
            -> Nested loop inner join  (cost=1741.42 rows=310) (actual time=0.151..2.826 rows=310 loops=1)
                -> Index scan on jobs using job_creation_date_INDEX (reverse)  (cost=5.42 rows=310) (actual time=0.132..1.623 rows=310 loops=1)
                -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=1 loops=310)
            -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=310)
        -> Single-row covering index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=310)
'
'-> Limit/Offset: 10/300 row(s)  (cost=8613.25 rows=0) (actual time=86.843..86.843 rows=0 loops=1)
    -> Aggregate: group_concat(tags.tag_name separator '','')  (cost=8613.25 rows=1) (actual time=86.841..86.841 rows=1 loops=1)
        -> Nested loop left join  (cost=7931.25 rows=6820) (actual time=0.093..83.736 rows=7396 loops=1)
            -> Nested loop left join  (cost=5544.25 rows=6820) (actual time=0.090..77.238 rows=7396 loops=1)
                -> Nested loop inner join  (cost=3157.25 rows=6820) (actual time=0.080..55.773 rows=7396 loops=1)
                    -> Table scan on jobs  (cost=770.25 rows=6820) (actual time=0.057..17.499 rows=7396 loops=1)
                    -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.005..0.005 rows=1 loops=7396)
                -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=7396)
            -> Single-row index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=7396)
'


'-> Limit/Offset: 10/300 row(s)  (actual time=105.025..105.029 rows=10 loops=1)
    -> Sort: jobs.job_creation_date DESC, limit input to 310 row(s) per chunk  (actual time=104.838..105.008 rows=310 loops=1)
        -> Stream results  (cost=8613.25 rows=6820) (actual time=0.129..89.437 rows=7396 loops=1)
            -> Group (no aggregates)  (cost=8613.25 rows=6820) (actual time=0.121..70.949 rows=7396 loops=1)
                -> Nested loop left join  (cost=7931.25 rows=6820) (actual time=0.100..60.138 rows=7396 loops=1)
                    -> Nested loop left join  (cost=5544.25 rows=6820) (actual time=0.097..55.504 rows=7396 loops=1)
                        -> Nested loop inner join  (cost=3157.25 rows=6820) (actual time=0.086..40.345 rows=7396 loops=1)
                            -> Index scan on jobs using PRIMARY  (cost=770.25 rows=6820) (actual time=0.063..13.155 rows=7396 loops=1)
                            -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=1 loops=7396)
                        -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=7396)
                    -> Single-row covering index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=7396)
'

'-> Limit/Offset: 10/300 row(s)  (actual time=95.298..95.303 rows=10 loops=1)
    -> Sort: jobs.job_creation_date DESC, limit input to 310 row(s) per chunk  (actual time=95.069..95.278 rows=310 loops=1)
        -> Stream results  (cost=8613.25 rows=6820) (actual time=0.089..79.575 rows=7396 loops=1)
            -> Group (no aggregates)  (cost=8613.25 rows=6820) (actual time=0.082..63.132 rows=7396 loops=1)
                -> Nested loop left join  (cost=7931.25 rows=6820) (actual time=0.066..53.862 rows=7396 loops=1)
                    -> Nested loop left join  (cost=5544.25 rows=6820) (actual time=0.063..49.850 rows=7396 loops=1)
                        -> Nested loop inner join  (cost=3157.25 rows=6820) (actual time=0.055..35.941 rows=7396 loops=1)
                            -> Index scan on jobs using PRIMARY  (cost=770.25 rows=6820) (actual time=0.037..11.261 rows=7396 loops=1)
                            -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=1 loops=7396)
                        -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=7396)
                    -> Single-row covering index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=7396)
'

'-> Limit/Offset: 10/300 row(s)  (cost=5213.42 rows=10) (actual time=4.805..4.941 rows=10 loops=1)
    -> Nested loop left join  (cost=5213.42 rows=310) (actual time=0.289..4.903 rows=310 loops=1)
        -> Nested loop left join  (cost=3477.42 rows=310) (actual time=0.284..4.591 rows=310 loops=1)
            -> Nested loop inner join  (cost=1741.42 rows=310) (actual time=0.259..3.683 rows=310 loops=1)
                -> Index scan on jobs using job_creation_date_INDEX  (cost=5.42 rows=310) (actual time=0.179..2.075 rows=310 loops=1)
                -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.005..0.005 rows=1 loops=310)
            -> Covering index lookup on job_tags using PRIMARY (job_id=jobs.job_id)  (cost=0.25 rows=1) (actual time=0.002..0.002 rows=0 loops=310)
        -> Single-row covering index lookup on tags using PRIMARY (tag_id=job_tags.tag_id)  (cost=0.25 rows=1) (actual time=0.000..0.000 rows=0 loops=310)
'

'-> Limit/Offset: 10/300 row(s)  (cost=1741.42 rows=10) (actual time=3.463..3.603 rows=10 loops=1)
    -> Nested loop inner join  (cost=1741.42 rows=310) (actual time=0.089..3.560 rows=310 loops=1)
        -> Index scan on jobs using job_creation_date_INDEX  (cost=5.42 rows=310) (actual time=0.073..2.074 rows=310 loops=1)
        -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=310)
'
*/
-- SELECT COUNT(*) FROM jobs;

/*



/*

'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=8.719..8.732 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=8.666..8.722 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2021-04-02 03:03:03'')  (cost=189.55 rows=1653) (actual time=0.045..4.653 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.044..3.980 rows=1720 loops=1)
'
'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=9.891..9.904 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=9.829..9.891 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''1190-04-02 03:03:03'')  (cost=189.55 rows=1653) (actual time=0.083..4.300 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.081..3.513 rows=1720 loops=1)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=8.657..8.673 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=8.592..8.657 rows=110 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.056..3.847 rows=1720 loops=1)
'

'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=0.665..0.715 rows=10 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-02 03:03:03'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-02 03:03:03'')  (cost=189.55 rows=1070) (actual time=0.051..0.703 rows=110 loops=1)
'

'-> Limit/Offset: 10/100 row(s)  (cost=481.76 rows=10) (actual time=0.805..0.849 rows=10 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-02 03:03:03'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-02 03:03:03'')  (cost=481.76 rows=1070) (actual time=0.254..0.837 rows=110 loops=1)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=0.884..0.938 rows=10 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-02 03:03:03'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-02 03:03:03'')  (cost=189.55 rows=1070) (actual time=0.036..0.758 rows=110 loops=1)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=9.121..9.136 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=9.060..9.123 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2021-02-02 03:03:03'')  (cost=189.55 rows=1653) (actual time=0.050..4.640 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.048..3.965 rows=1720 loops=1)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=9.630..9.644 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=9.567..9.630 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2021-02-02 03:03:03'')  (cost=189.55 rows=1653) (actual time=0.042..3.988 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.040..3.346 rows=1720 loops=1)
'




'-> Limit/Offset: 10/100 row(s)  (cost=768.10 rows=10) (actual time=13.167..13.242 rows=10 loops=1)
    -> Nested loop inner join  (cost=768.10 rows=1653) (actual time=12.646..13.226 rows=110 loops=1)
        -> Sort: jobs.job_creation_date DESC  (cost=189.55 rows=1653) (actual time=12.607..12.706 rows=110 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.051..3.911 rows=1720 loops=1)
        -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.004..0.004 rows=1 loops=110)
'
'-> Limit/Offset: 10/100 row(s)  (cost=768.10 rows=10) (actual time=12.538..12.589 rows=10 loops=1)
    -> Nested loop inner join  (cost=768.10 rows=1653) (actual time=12.125..12.577 rows=110 loops=1)
        -> Sort: jobs.job_creation_date DESC  (cost=189.55 rows=1653) (actual time=12.081..12.156 rows=110 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.077..5.016 rows=1720 loops=1)
        -> Single-row index lookup on users using PRIMARY (user_id=jobs.user_id)  (cost=0.25 rows=1) (actual time=0.003..0.003 rows=1 loops=110)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=6.282..6.294 rows=10 loops=1)
    -> Sort: jobs.job_creation_date DESC, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=6.231..6.283 rows=110 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.052..2.775 rows=1720 loops=1)
'

'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=8.531..8.544 rows=10 loops=1)
    -> Sort: jobs.job_creation_date DESC, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=8.477..8.533 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2021-02-02 03:03:03'')  (cost=189.55 rows=1653) (actual time=0.049..4.114 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.048..3.529 rows=1720 loops=1)
'

'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=9.873..9.887 rows=10 loops=1)
    -> Sort: jobs.job_creation_date DESC, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=9.814..9.875 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2021-02-02 03:03:03'')  (cost=189.55 rows=1653) (actual time=0.063..4.403 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.061..3.741 rows=1720 loops=1)
'


-- SELECT * FROM tables_records_counts;
EXPLAIN ANALYZE FORMAT = TREE
SELECT `freelancing project`.`email_verification_codes`.verification_code 
                                      FROM `freelancing project`.`email_verification_codes`
                                      ORDER BY `freelancing project`.`email_verification_codes`.verification_code
                                      LIMIT 1;

EXPLAIN ANALYZE
SELECT `freelancing project`.`email_verification_codes`.verification_code 
FROM `freelancing project`.`email_verification_codes`
LIMIT 1;

'-> Limit: 1 row(s)  (cost=0.00 rows=1) (actual time=0.026..0.027 rows=1 loops=1)
    -> Covering index scan on email_verification_codes using PRIMARY  (cost=0.00 rows=1) (actual time=0.026..0.026 rows=1 loops=1)
'


EXPLAIN ANALYZE FORMAT = TREE
SELECT *
FROM `freelancing project`.`jobs`
ORDER BY job_id DESC
LIMIT 33 OFFSET 1690; 

EXPLAIN FORMAT = TREE
SELECT * FROM jobs ORDER BY job_creation_date LIMIT 1 OFFSET 999;

EXPLAIN ANALYZE FORMAT = TREE
SELECT *
FROM `freelancing project`.`jobs` FORCE INDEX (job_creation_date_INDEX)
ORDER BY job_creation_date DESC
LIMIT 33 OFFSET 1690;

EXPLAIN FORMAT = TREE
SELECT *
FROM `freelancing project`.`jobs` FORCE INDEX (job_creation_date_INDEX)
WHERE job_creation_date > '2023-04-28 05:27:34'
ORDER BY job_creation_date DESC
LIMIT 10;



EXPLAIN ANALYZE FORMAT = TREE
SELECT *
FROM `freelancing project`.`jobs`
WHERE job_id > 500
ORDER BY job_id ASC
LIMIT 1;
'-> Limit: 1 row(s)  (cost=165.99 rows=1) (actual time=0.037..0.037 rows=1 loops=1)
    -> Filter: (jobs.job_id > 500)  (cost=165.99 rows=826) (actual time=0.036..0.036 rows=1 loops=1)
        -> Index range scan on jobs using PRIMARY over (500 < job_id)  (cost=165.99 rows=826) (actual time=0.034..0.034 rows=1 loops=1)
'

'-> Limit: 1 row(s)  (cost=165.99 rows=1) (actual time=0.039..0.039 rows=1 loops=1)
    -> Filter: (jobs.job_id > 500)  (cost=165.99 rows=826) (actual time=0.039..0.039 rows=1 loops=1)
        -> Index range scan on jobs using PRIMARY over (500 < job_id)  (cost=165.99 rows=826) (actual time=0.037..0.037 rows=1 loops=1)
'


EXPLAIN ANALYZE FORMAT = TREE
SELECT COUNT(*)
FROM `freelancing project`.`jobs`
WHERE job_creation_date > '2023-05-28 05:27:34' AND job_creation_date < '2023-10-28 05:27:34';
'-> Aggregate: count(0)  (cost=193.94 rows=1) (actual time=0.849..0.849 rows=1 loops=1)
    -> Filter: ((jobs.job_creation_date > TIMESTAMP''2023-04-28 05:27:34'') and (jobs.job_creation_date < TIMESTAMP''2023-10-28 05:27:34''))  (cost=129.44 rows=645) (actual time=0.039..0.776 rows=645 loops=1)
        -> Covering index range scan on jobs using job_creation_date_INDEX over (''2023-04-28 05:27:34'' < job_creation_date < ''2023-10-28 05:27:34'')  (cost=129.44 rows=645) (actual time=0.036..0.514 rows=645 loops=1)
'
'-> Aggregate: count(0)  (cost=29.69 rows=1) (actual time=0.155..0.156 rows=1 loops=1)
    -> Filter: ((jobs.job_creation_date > TIMESTAMP''2023-05-28 05:27:34'') and (jobs.job_creation_date < TIMESTAMP''2023-10-28 05:27:34''))  (cost=19.89 rows=98) (actual time=0.027..0.138 rows=98 loops=1)
        -> Covering index range scan on jobs using job_creation_date_INDEX over (''2023-05-28 05:27:34'' < job_creation_date < ''2023-10-28 05:27:34'')  (cost=19.89 rows=98) (actual time=0.023..0.098 rows=98 loops=1)
'
EXPLAIN ANALYZE FORMAT = TREE
SELECT *
FROM `freelancing project`.`jobs`
WHERE job_creation_date > '2023-04-28 05:27:34';
'-> Filter: (jobs.job_creation_date > TIMESTAMP''2023-04-28 05:27:34'')  (cost=189.55 rows=645) (actual time=0.156..4.205 rows=645 loops=1)
    -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.141..3.736 rows=1720 loops=1)
'

EXPLAIN ANALYZE FORMAT = TREE
SELECT COUNT(*)
FROM `freelancing project`.`jobs`
WHERE job_creation_date > '2023-04-28 05:27:34';
'-> Aggregate: count(0)  (cost=193.94 rows=1) (actual time=0.807..0.807 rows=1 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''2023-04-28 05:27:34'')  (cost=129.44 rows=645) (actual time=0.034..0.718 rows=645 loops=1)
        -> Covering index range scan on jobs using job_creation_date_INDEX over (''2023-04-28 05:27:34'' < job_creation_date)  (cost=129.44 rows=645) (actual time=0.032..0.512 rows=645 loops=1)
'

EXPLAIN ANALYZE FORMAT = TREE
SELECT * FROM `freelancing project`.jobs
-- WHERE job_creation_date > '2023-04-27 12:26:02'
-- ORDER BY job_creation_date
LIMIT 10 OFFSET 10;
'-> Limit/Offset: 10/10 row(s)  (cost=189.55 rows=10) (actual time=0.072..0.117 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1900-04-27 12:26:02'')  (cost=189.55 rows=1653) (actual time=0.044..0.112 rows=20 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.042..0.089 rows=20 loops=1)
'


'-> Limit: 10 row(s)  (cost=0.15 rows=10) (actual time=0.098..0.168 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1900-04-27 12:26:02'')  (cost=0.15 rows=10) (actual time=0.095..0.164 rows=10 loops=1)
        -> Index scan on jobs using job_creation_date_INDEX  (cost=0.15 rows=10) (actual time=0.092..0.157 rows=10 loops=1)
'


'-> Limit: 100 row(s)  (cost=189.55 rows=100) (actual time=0.034..1.066 rows=100 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-27 12:26:02'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-27 12:26:02'')  (cost=189.55 rows=655) (actual time=0.033..1.050 rows=100 loops=1)
'
'-> Limit: 100 row(s)  (cost=189.55 rows=100) (actual time=7.497..7.562 rows=100 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 100 row(s) per chunk  (cost=189.55 rows=1653) (actual time=7.496..7.545 rows=100 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''1900-04-27 12:26:02'')  (cost=189.55 rows=1653) (actual time=0.048..3.714 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.046..3.259 rows=1720 loops=1)
'


'-> Limit: 50 row(s)  (cost=0.88 rows=50) (actual time=0.095..0.445 rows=50 loops=1)
    -> Index scan on jobs using job_creation_date_INDEX  (cost=0.88 rows=50) (actual time=0.094..0.436 rows=50 loops=1)
'


'-> Limit: 100 row(s)  (cost=189.55 rows=100) (actual time=9.178..9.249 rows=100 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 100 row(s) per chunk  (cost=189.55 rows=1653) (actual time=9.176..9.233 rows=100 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.056..3.668 rows=1720 loops=1)
'


'-> Limit: 120 row(s)  (cost=189.55 rows=120) (actual time=8.540..8.649 rows=120 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 120 row(s) per chunk  (cost=189.55 rows=1653) (actual time=8.539..8.626 rows=120 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.055..4.159 rows=1720 loops=1)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=8.835..8.870 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=8.764..8.854 rows=110 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.052..3.840 rows=1720 loops=1)
'

'-> Limit: 10 row(s)  (cost=189.55 rows=10) (actual time=0.041..0.066 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1990-04-27 12:26:02'')  (cost=189.55 rows=1653) (actual time=0.041..0.064 rows=10 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.039..0.060 rows=10 loops=1)
'

'-> Limit: 10 row(s)  (cost=0.15 rows=10) (actual time=0.096..0.149 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1990-04-27 12:26:02'')  (cost=0.15 rows=10) (actual time=0.094..0.146 rows=10 loops=1)
        -> Index scan on jobs using job_creation_date_INDEX  (cost=0.15 rows=10) (actual time=0.091..0.140 rows=10 loops=1)
'


'-> Limit: 10 row(s)  (cost=189.55 rows=10) (actual time=0.050..0.082 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1990-04-27 12:26:02'')  (cost=189.55 rows=1653) (actual time=0.049..0.079 rows=10 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.048..0.074 rows=10 loops=1)
'

'-> Limit: 10 row(s)  (cost=189.55 rows=10) (actual time=0.047..0.077 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1990-04-27 12:26:02'')  (cost=189.55 rows=1653) (actual time=0.046..0.075 rows=10 loops=1)
        -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.045..0.070 rows=10 loops=1)
'

'-> Limit: 10 row(s)  (cost=0.15 rows=10) (actual time=0.094..0.139 rows=10 loops=1)
    -> Filter: (jobs.job_creation_date > TIMESTAMP''1990-04-27 12:26:02'')  (cost=0.15 rows=10) (actual time=0.093..0.136 rows=10 loops=1)
        -> Index scan on jobs using job_creation_date_INDEX  (cost=0.15 rows=10) (actual time=0.091..0.131 rows=10 loops=1)
'


'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=0.838..0.886 rows=10 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-27 12:26:02'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-27 12:26:02'')  (cost=189.55 rows=655) (actual time=0.078..0.872 rows=110 loops=1)
'

SELECT MIN(job_creation_date) FROM jobs;

'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=8.726..8.741 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=8.658..8.726 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2023-02-26 12:26:02'')  (cost=189.55 rows=1653) (actual time=0.141..4.326 rows=1719 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.134..3.795 rows=1720 loops=1)
'




'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=10.119..10.134 rows=10 loops=1)
    -> Sort: jobs.job_creation_date, limit input to 110 row(s) per chunk  (cost=189.55 rows=1653) (actual time=10.051..10.121 rows=110 loops=1)
        -> Filter: (jobs.job_creation_date > TIMESTAMP''2022-04-28 05:27:34'')  (cost=189.55 rows=1653) (actual time=0.049..5.115 rows=1720 loops=1)
            -> Table scan on jobs  (cost=189.55 rows=1653) (actual time=0.048..4.384 rows=1720 loops=1)
'
'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=0.735..0.787 rows=10 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-28 05:27:34'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-28 05:27:34'')  (cost=189.55 rows=645) (actual time=0.039..0.772 rows=110 loops=1)
'
'-> Limit/Offset: 10/100 row(s)  (cost=189.55 rows=10) (actual time=0.735..0.787 rows=10 loops=1)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-04-28 05:27:34'' < job_creation_date), with index condition: (jobs.job_creation_date > TIMESTAMP''2023-04-28 05:27:34'')  (cost=189.55 rows=645) (actual time=0.039..0.772 rows=110 loops=1)
'









EXPLAIN ANALYZE FORMAT = TREE
SELECT COUNT(*) FROM jobs;

SELECT * FROM jobs;

ANALYZE TABLE jobs;

*/

/*




'-> Index range scan on jobs using job_creation_date_INDEX over (''2023-05-28 05:27:34'' < job_creation_date < ''2023-10-28 05:27:34''), with index condition: ((jobs.job_creation_date > TIMESTAMP''2023-05-28 05:27:34'') and (jobs.job_creation_date < TIMESTAMP''2023-10-28 05:27:34''))  (cost=44.36 rows=98)
'
'-> Limit/Offset: 10/40 row(s)  (cost=44.36 rows=10)
    -> Index range scan on jobs using job_creation_date_INDEX over (''2023-05-28 05:27:34'' < job_creation_date < ''2023-10-28 05:27:34''), with index condition: ((jobs.job_creation_date > TIMESTAMP''2023-05-28 05:27:34'') and (jobs.job_creation_date < TIMESTAMP''2023-10-28 05:27:34''))  (cost=44.36 rows=98)
'
*/






/*
SELECT * FROM `freelancing project`.email_verification_codes LIMIT 1;
SELECT * FROM `freelancing project`.`email_verification_codes` ORDER BY `freelancing project`.`email_verification_codes`.verification_code ASC LIMIT 1;
SELECT * FROM `freelancing project`.email_verification_codes;

INSERT INTO `freelancing project`.`incomplete_registration_users` (user_email, user_first_name, user_last_name, user_password, user_verification_code) VALUES
();


DELETE FROM `freelancing project`.`email_verification_codes` WHERE `freelancing project`.`email_verification_codes`.verification_code = '111232739';

SELECT COUNT(*) FROM `freelancing project`.email_verification_codes;

*/






-- SHOW TABLES FROM `freelancing project`;



/*
SHOW VARIABLES LIKE 'ft_min_word_len';
SET GLOBAL ft_min_word_len = 2;

SHOW CREATE TABLE `tags`;

SHOW VARIABLES LIKE '%stopword%';

ALTER TABLE `tags`
  DROP INDEX `tag_name_FULLTEXT`,
  ADD FULLTEXT INDEX `tag_name_FULLTEXT` (`tag_name`(2)) VISIBLE;


SELECT * FROM `freelancing project`.email_verification_codes;
INSTALL PLUGIN ngram SONAME 'ha_ngram.so';
SELECT * FROM jobs
WHERE MATCH(job_title, job_description) AGAINST('ui');

ALTER TABLE `Freelancing Project`.`tags`
DROP INDEX `tag_name_FULLTEXT`,
ADD FULLTEXT INDEX `tag_name_FULLTEXT` (`tag_name`(2))
WITH PARSER ngram;

SELECT * FROM tags
WHERE MATCH(tag_name) AGAINST('PHP' WITH QUERY EXPANSION) ORDER BY MATCH(tag_name) AGAINST('PHP' IN NATURAL LANGUAGE MODE) ASC;

SHOW INDEX FROM `tags`;

SHOW VARIABLES LIKE 'ft_stopword_file';


SET GLOBAL ft_stopword_file = '/path/to/my_stopwords.txt';
SET GLOBAL ft_stopword = ('+', '-');

SELECT COUNT(DISTINCT tag_name) AS num_distinct_tags FROM tags;

ANALYZE TABLE tags;

CREATE FULLTEXT INDEX idx_jobs_title_description ON jobs(job_title(2), job_description(2));
*/
/*

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('Ux' IN BOOLEAN MODE);
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST("UI" IN BOOLEAN MODE);
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST("'UI'" IN BOOLEAN MODE);
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('"UI"' IN BOOLEAN MODE);


SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('' IN NATURAL LANGUAGE MODE);
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST("UIm" IN NATURAL LANGUAGE MODE);
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST("'UI'" IN NATURAL LANGUAGE MODE);
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('"UI"' IN NATURAL LANGUAGE MODE);




SELECT * FROM tags WHERE tag_name LIKE '%a%';

SHOW INDEX FROM tags;

INSERT INTO tags (tag_name) VALUES ("UIM");

ANALYZE TABLE tags;

DROP TABLE tags;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `Freelancing Project`.`tags` (
  `tag_id` INT NOT NULL AUTO_INCREMENT,
  `tag_name` VARCHAR(100) NOT NULL,
  `tag_name_ngram` VARCHAR(100) GENERATED ALWAYS AS (`tag_name`) STORED,
  PRIMARY KEY (`tag_id`),
  UNIQUE INDEX `tag_name_UNIQUE` (`tag_name` ASC) VISIBLE,
  FULLTEXT INDEX `tag_name_FULLTEXT` (`tag_name`) VISIBLE,
  FULLTEXT INDEX `tag_name_ngram_FULLTEXT` (`tag_name_ngram`) VISIBLE WITH PARSER ngram
)
ENGINE = InnoDB;

SELECT CONVERT_TZ(NOW(), '+00:00', '+03:00') AS current_time_gmt_plus_3;
SELECT CONVERT_TZ(NOW(), '+00:00', 'GMT+3') AS current_time_gmt_plus_3;
SELECT CONVERT_TZ(NOW(), '+00:00', 'GMT+3') AS current_time_gmt_plus_3;

*/

/*
SELECT count(*) FROM `Freelancing Project`.`incomplete_registration_users`;
SELECT * FROM `Freelancing Project`.`incomplete_registration_users` ORDER BY `Freelancing Project`.`incomplete_registration_users`.inserted_at;

ALTER TABLE `Freelancing Project`.`incomplete_registration_users`
ADD COLUMN `inserted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD INDEX `idx_inserted_at` (`inserted_at`);

CREATE EVENT IF NOT EXISTS `delete_old_records`
ON SCHEDULE EVERY 30 MINUTE
DO
  DELETE FROM `Freelancing Project`.`incomplete_registration_users`
  WHERE `Freelancing Project`.`incomplete_registration_users`.inserted_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);
  
EXPLAIN FORMAT=tree
DELETE FROM `Freelancing Project`.`incomplete_registration_users`
WHERE `Freelancing Project`.`incomplete_registration_users`.inserted_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);

EXPLAIN
DELETE FROM `Freelancing Project`.`incomplete_registration_users`
WHERE `Freelancing Project`.`incomplete_registration_users`.inserted_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);

EXPLAIN FORMAT=tree
SELECT * FROM `Freelancing Project`.`incomplete_registration_users`
WHERE `Freelancing Project`.`incomplete_registration_users`.inserted_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE);
*/

/*
'-> Index range scan on incomplete_registration_users using idx_inserted_at over (inserted_at < ''2023-05-28 04:39:38''), with index condition: (incomplete_registration_users.inserted_at < <cache>((now() - interval 30 minute)))  (cost=0.71 rows=1)
'
*/

/*
INSERT INTO `Freelancing Project`.`incomplete_registration_users`
(`user_email`, `user_first_name`, `user_last_name`, `user_password`, `user_verification_code`, `inserted_at`)
VALUES
('email1@example.com', 'John', 'Doe', 'password1', 'abc123', DATE_ADD(NOW(), INTERVAL 600 MINUTE)),
('email2@example.com', 'Mary', 'Smith', 'password2', 'def456', DATE_ADD(NOW(), INTERVAL 570 MINUTE)),
('email3@example.com', 'James', 'Johnson', 'password3', 'ghi789', DATE_ADD(NOW(), INTERVAL 540 MINUTE)),
('email4@example.com', 'Sarah', 'Jones', 'password4', 'jkl012', DATE_ADD(NOW(), INTERVAL 510 MINUTE)),
('email5@example.com', 'David', 'Brown', 'password5', 'mno345', DATE_ADD(NOW(), INTERVAL 480 MINUTE)),
('email6@example.com', 'Emily', 'Wilson', 'password6', 'pqr678', DATE_ADD(NOW(), INTERVAL 450 MINUTE)),
('email7@example.com', 'Daniel', 'Miller', 'password7', 'stu901', DATE_ADD(NOW(), INTERVAL 420 MINUTE)),
('email8@example.com', 'Jessica', 'Taylor', 'password8', 'vwx234', DATE_ADD(NOW(), INTERVAL 390 MINUTE)),
('email9@example.com', 'Michael', 'Anderson', 'password9', 'yzm567', DATE_ADD(NOW(), INTERVAL 360 MINUTE)),
('email10@example.com', 'Ava', 'Clark', 'password10', 'abc890', DATE_ADD(NOW(), INTERVAL 330 MINUTE)),
('email11@example.com', 'William', 'Allen', 'password11', 'def123', DATE_ADD(NOW(), INTERVAL 300 MINUTE)),
('email12@example.com', 'Sophia', 'Young', 'password12', 'ghi456', DATE_ADD(NOW(), INTERVAL 270 MINUTE)),
('email13@example.com', 'Christopher', 'King', 'password13', 'jkl789', DATE_ADD(NOW(), INTERVAL 240 MINUTE)),
('email14@example.com', 'Olivia', 'Wright', 'password14', 'mno012', DATE_ADD(NOW(), INTERVAL 210 MINUTE)),
('email15@example.com', 'Matthew', 'Scott', 'password15', 'pqr345', DATE_ADD(NOW(), INTERVAL 180 MINUTE)),
('email16@example.com', 'Emma', 'Green', 'password16', 'stu678', DATE_ADD(NOW(), INTERVAL 150 MINUTE)),
('email17@example.com', 'Andrew', 'Baker', 'password17', 'vwx901', DATE_ADD(NOW(), INTERVAL 120 MINUTE)),
('email18@example.com', 'Isabella', 'Hill', 'password18', 'yzm234', DATE_ADD(NOW(), INTERVAL 90 MINUTE)),
('email19@example.com', 'Ethan', 'Carter', 'password19', 'abc567', DATE_ADD(NOW(), INTERVAL 60 MINUTE)),
('email20@example.com', 'Mia', 'Mitchell', 'password20', 'def890', DATE_ADD(NOW(), INTERVAL 30 MINUTE));
-- I need to update the database for the last time and to put a datetime column in the incomplete_registration_users table to make an event

SELECT DATE_ADD(UTC_TIMESTAMP(), INTERVAL 3 HOUR);

SHOW EVENTS;

SELECT NOW();


SELECT * FROM JOBS;
SELECT job_description FROM jobs LIMIT 4 OFFSET 10;









*/






/*

CREATE TABLE IF NOT EXISTS `Freelancing Project`.completed_jobs (
job_id INT NOT NULL AUTO_INCREMENT,
user_id INT NOT NULL,
job_title VARCHAR(250) NOT NULL,
job_description VARCHAR(4000) NOT NULL,
job_creation_date DATETIME NOT NULL,
job_deadline_date DATETIME NOT NULL,
job_price INT NULL,
freelancer_id INT NULL,
job_completion_date DATETIME NULL,
job_completion_rating INT NULL,
PRIMARY KEY (job_id, user_id),
INDEX user_id_INDEX (user_id ASC) VISIBLE,
INDEX freelancer_id_INDEX (freelancer_id ASC) VISIBLE,
INDEX job_price_INDEX (job_price ASC) VISIBLE,
INDEX job_creation_date_INDEX (job_creation_date ASC) VISIBLE,
CONSTRAINT fk_completed_jobs_users2
FOREIGN KEY (user_id)
REFERENCES `Freelancing Project`.users (user_id)
ON DELETE CASCADE
ON UPDATE CASCADE,
CONSTRAINT fk_completed_jobs_freelancers1
FOREIGN KEY (freelancer_id)
REFERENCES `Freelancing Project`.freelancers (freelancer_id)
ON DELETE SET NULL
ON UPDATE CASCADE)
ENGINE = InnoDB;


USE `freelancing project`;
DELIMITER $$

CREATE EVENT IF NOT EXISTS `move_completed_jobs`
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
  DECLARE num_rows INT;
SELECT 
    COUNT(*)
INTO num_rows FROM
    `jobs`
WHERE
    `job_deadline_date` < NOW();
  IF num_rows > 0 THEN
    INSERT INTO `completed_jobs` (`job_id`, `user_id`, `job_title`, `job_description`, `job_creation_date`,
                                  `job_deadline_date`, `job_price`, `freelancer_id`, `job_completion_date`,
                                  `job_completion_rating`)
    SELECT `job_id`, `user_id`, `job_title`, `job_description`, `job_creation_date`, `job_deadline_date`,
           `job_price`, `freelancer_id`, NOW(), NULL
    FROM `jobs`
    WHERE `job_deadline_date` < NOW();
DELETE FROM `jobs` 
WHERE
    `job_deadline_date` < NOW();
  END IF;
END$$

DELIMITER ;

SHOW EVENTS FROM `freelancing project`;

*/

/*
SELECT NOW();


EXPLAIN format=tree SELECT 
      * 
    FROM 
      `freelancing project`.`jobs` 
    WHERE 
      `freelancing project`.`jobs`.job_id > 300;

EXPLAIN FORMAT = TREE SELECT 
  `freelancing project`.`jobs`.job_id, 
  `freelancing project`.`users`.user_id AS publisher_id, 
  `freelancing project`.`users`.user_first_name AS publisher_first_name, 
  `freelancing project`.`users`.user_last_name AS publisher_last_name, 
  `freelancing project`.`jobs`.job_title, 
  `freelancing project`.`jobs`.job_creation_date, 
  `freelancing project`.`jobs`.job_description, 
  `freelancing project`.`jobs`.job_deadline_date, 
  `freelancing project`.`jobs`.job_price, 
  GROUP_CONCAT(
    `freelancing project`.`tags`.tag_name
  ) AS job_tags, 
  `freelancing project`.`users`.user_profile_picture_path AS publisher_profile_picture 
FROM 
  (
    SELECT 
      * 
    FROM 
      `freelancing project`.`jobs` 
    WHERE 
      `freelancing project`.`jobs`.freelancer_id IS NULL
  ) AS jobs
  INNER JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id 
  LEFT JOIN `freelancing project`.`job_tags` ON `freelancing project`.`jobs`.job_id = `freelancing project`.`job_tags`.job_id 
  LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id 
GROUP BY 
  `freelancing project`.`jobs`.job_id 
ORDER BY 
  `freelancing project`.`jobs`.job_creation_date DESC;
*/
/*

'-> Sort row IDs: (case when (users.user_first_name like ''%ahmad%'') then 1 when (users.user_last_name like ''%ahmad%'') then 2 else 3 end)  (cost=0.35 rows=1)
    -> Filter: (match users.user_first_name,users.user_last_name against (''ahmad''))
        -> Full-text index search on users using user_full_name_FULLTEXT (user_first_name=''ahmad'')
'

*/

/*

EXPLAIN FORMAT = TREE SELECT *
FROM users
WHERE MATCH(user_first_name, user_last_name) AGAINST('ahmad');

EXPLAIN FORMAT = TREE SELECT *, 
  CASE 
    WHEN user_first_name LIKE '%ahmad%' THEN 1
    WHEN user_last_name LIKE '%ahmad%' THEN 2
    ELSE 3
  END AS priority
FROM users
WHERE MATCH(user_first_name, user_last_name) AGAINST('ahmad')
ORDER BY priority;

SHOW INDEX FROM users;

SELECT * FROM `freelancing project`.users WHERE MATCH(user_first_name, user_last_name) AGAINST('ahmad');
SELECT * FROM users WHERE MATCH(user_first_name) AGAINST('Ahmad' IN BOOLEAN MODE);

EXPLAIN ANALYZE SELECT * FROM users WHERE MATCH(user_first_name, user_last_name) AGAINST('Ali' IN BOOLEAN MODE);

SELECT * FROM users WHERE MATCH(user_last_name) AGAINST('Ahmad');

SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_INDEX_TABLE WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'user_first_name_FULLTEXT';

SELECT * FROM users;

SHOW INDEX FROM users;

*/


/*
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('User');

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST("C++" IN BOOLEAN MODE);

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('"C++"' IN BOOLEAN MODE);

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST ('"ASP   NET"' IN BOOLEAN MODE);


SELECT * FROM tags WHERE MATCH(tag_name) AGAINST(''"C++"'' IN BOOLEAN MODE) LIMIT 0, 1000;

*/


/*
INSERT INTO `freelancing project`.`jobs` (user_id, job_title, job_description, job_creation_date, job_deadline_date, job_price)
VALUES (
    1,
    'example_job_title',
    'example_job_description',
    '2023-06-01 12:00:00',
    '2023-07-01',
    10000
);

SELECT * FROM jobs;
SELECT * FROM users;
SELECT MAX(job_id) AS max_id FROM `freelancing project`.`jobs`;
DELETE FROM tags where tag_id > 512;
DELETE FROM jobs where job_id > 172;
SHOW TABLE STATUS WHERE Name = 'jobs';
ANALYZE TABLE jobs;
SHOW TABLE STATUS WHERE Name = 'users';
ALTER TABLE `freelancing project`.`jobs` AUTO_INCREMENT = 1;
SELECT * FROM job_tags;




SELECT AUTO_INCREMENT
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'freelancing project'
  AND TABLE_NAME = 'jobs';

*/
/*

CREATE TABLE IF NOT EXISTS `Freelancing Project`.completed_jobs (
job_id INT NOT NULL AUTO_INCREMENT,
user_id INT NOT NULL,
job_title VARCHAR(250) NOT NULL,
job_description VARCHAR(4000) NOT NULL,
job_creation_date DATETIME NOT NULL,
job_deadline_date DATETIME NOT NULL,
job_price INT NULL,
freelancer_id INT NULL,
job_completion_date DATETIME NULL,
job_completion_rating INT NULL,
PRIMARY KEY (job_id, user_id),
INDEX user_id_INDEX (user_id ASC) VISIBLE,
INDEX freelancer_id_INDEX (freelancer_id ASC) VISIBLE,
INDEX job_price_INDEX (job_price ASC) VISIBLE,
INDEX job_creation_date_INDEX (job_creation_date ASC) VISIBLE,
CONSTRAINT fk_completed_jobs_users2
FOREIGN KEY (user_id)
REFERENCES `Freelancing Project`.users (user_id)
ON DELETE CASCADE
ON UPDATE CASCADE,
CONSTRAINT fk_completed_jobs_freelancers1
FOREIGN KEY (freelancer_id)
REFERENCES `Freelancing Project`.freelancers (freelancer_id)
ON DELETE SET NULL
ON UPDATE CASCADE)
ENGINE = InnoDB;


USE `freelancing project`;
DELIMITER $$

CREATE EVENT IF NOT EXISTS `move_completed_jobs`
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
  DECLARE num_rows INT;
SELECT 
    COUNT(*)
INTO num_rows FROM
    `jobs`
WHERE
    `job_deadline_date` < NOW();
  IF num_rows > 0 THEN
    INSERT INTO `completed_jobs` (`job_id`, `user_id`, `job_title`, `job_description`, `job_creation_date`,
                                  `job_deadline_date`, `job_price`, `freelancer_id`, `job_completion_date`,
                                  `job_completion_rating`)
    SELECT `job_id`, `user_id`, `job_title`, `job_description`, `job_creation_date`, `job_deadline_date`,
           `job_price`, `freelancer_id`, NOW(), NULL
    FROM `jobs`
    WHERE `job_deadline_date` < NOW();
DELETE FROM `jobs` 
WHERE
    `job_deadline_date` < NOW();
  END IF;
END$$

DELIMITER ;

SHOW EVENTS FROM `freelancing project`;

*/

/*
SELECT NOW();


EXPLAIN format=tree SELECT 
      * 
    FROM 
      `freelancing project`.`jobs` 
    WHERE 
      `freelancing project`.`jobs`.job_id > 300;

EXPLAIN FORMAT = TREE SELECT 
  `freelancing project`.`jobs`.job_id, 
  `freelancing project`.`users`.user_id AS publisher_id, 
  `freelancing project`.`users`.user_first_name AS publisher_first_name, 
  `freelancing project`.`users`.user_last_name AS publisher_last_name, 
  `freelancing project`.`jobs`.job_title, 
  `freelancing project`.`jobs`.job_creation_date, 
  `freelancing project`.`jobs`.job_description, 
  `freelancing project`.`jobs`.job_deadline_date, 
  `freelancing project`.`jobs`.job_price, 
  GROUP_CONCAT(
    `freelancing project`.`tags`.tag_name
  ) AS job_tags, 
  `freelancing project`.`users`.user_profile_picture_path AS publisher_profile_picture 
FROM 
  (
    SELECT 
      * 
    FROM 
      `freelancing project`.`jobs` 
    WHERE 
      `freelancing project`.`jobs`.freelancer_id IS NULL
  ) AS jobs
  INNER JOIN `freelancing project`.`users` ON `freelancing project`.`jobs`.user_id = `freelancing project`.`users`.user_id 
  LEFT JOIN `freelancing project`.`job_tags` ON `freelancing project`.`jobs`.job_id = `freelancing project`.`job_tags`.job_id 
  LEFT JOIN `freelancing project`.`tags` ON `freelancing project`.`job_tags`.tag_id = `freelancing project`.`tags`.tag_id 
GROUP BY 
  `freelancing project`.`jobs`.job_id 
ORDER BY 
  `freelancing project`.`jobs`.job_creation_date DESC;
*/
/*

'-> Sort row IDs: (case when (users.user_first_name like ''%ahmad%'') then 1 when (users.user_last_name like ''%ahmad%'') then 2 else 3 end)  (cost=0.35 rows=1)
    -> Filter: (match users.user_first_name,users.user_last_name against (''ahmad''))
        -> Full-text index search on users using user_full_name_FULLTEXT (user_first_name=''ahmad'')
'

*/

/*

EXPLAIN FORMAT = TREE SELECT *
FROM users
WHERE MATCH(user_first_name, user_last_name) AGAINST('ahmad');

EXPLAIN FORMAT = TREE SELECT *, 
  CASE 
    WHEN user_first_name LIKE '%ahmad%' THEN 1
    WHEN user_last_name LIKE '%ahmad%' THEN 2
    ELSE 3
  END AS priority
FROM users
WHERE MATCH(user_first_name, user_last_name) AGAINST('ahmad')
ORDER BY priority;

SHOW INDEX FROM users;

SELECT * FROM `freelancing project`.users WHERE MATCH(user_first_name, user_last_name) AGAINST('ahmad');
SELECT * FROM users WHERE MATCH(user_first_name) AGAINST('Ahmad' IN BOOLEAN MODE);

EXPLAIN ANALYZE SELECT * FROM users WHERE MATCH(user_first_name, user_last_name) AGAINST('Ali' IN BOOLEAN MODE);

SELECT * FROM users WHERE MATCH(user_last_name) AGAINST('Ahmad');

SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_INDEX_TABLE WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'user_first_name_FULLTEXT';

SELECT * FROM users;

SHOW INDEX FROM users;

*/


/*
SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('User');

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST("C++" IN BOOLEAN MODE);

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST('"C++"' IN BOOLEAN MODE);

SELECT * FROM tags WHERE MATCH(tag_name) AGAINST ('"ASP   NET"' IN BOOLEAN MODE);


SELECT * FROM tags WHERE MATCH(tag_name) AGAINST(''"C++"'' IN BOOLEAN MODE) LIMIT 0, 1000;

*/

