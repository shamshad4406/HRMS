-- OPTIONAL one-time cleanup. REVIEW BEFORE RUNNING and take a backup first.
-- The old attendance form allowed several check-ins per employee per day
-- (e.g. employee 17 has 5 rows on 2026-10-25). The application now blocks this.
--
-- 1) See the duplicates:
SELECT personal_information_id, attendance_date, COUNT(*) AS rows_for_day,
       MIN(check_in) AS first_check_in, MAX(check_out) AS last_check_out
FROM attendances
GROUP BY personal_information_id, attendance_date
HAVING COUNT(*) > 1;

-- 2) Keep the earliest row of each day, copy the latest check-out onto it, delete the rest.
UPDATE attendances a
JOIN (
    SELECT personal_information_id, attendance_date,
           MIN(id) AS keep_id, MAX(check_out) AS last_out
    FROM attendances
    GROUP BY personal_information_id, attendance_date
    HAVING COUNT(*) > 1
) d ON d.keep_id = a.id
SET a.check_out = d.last_out;

DELETE a FROM attendances a
JOIN (
    SELECT personal_information_id, attendance_date, MIN(id) AS keep_id
    FROM attendances
    GROUP BY personal_information_id, attendance_date
    HAVING COUNT(*) > 1
) d ON d.personal_information_id = a.personal_information_id
   AND d.attendance_date = a.attendance_date
   AND a.id <> d.keep_id;

-- 3) Afterwards a unique key can be added (the app already prevents new duplicates):
-- ALTER TABLE attendances ADD UNIQUE KEY attendances_employee_date_unique (personal_information_id, attendance_date);
