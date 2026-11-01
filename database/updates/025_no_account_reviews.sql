-- New teacher accounts don't wait for an admin's OK any more (owner, 2026-10-27): whoever adds them (admin or
-- management), the account is PENDING until the teacher signs up and activates it, like every other account.
-- So user_accounts no longer needs update 012's review columns. (012's note says "config/reviews.php": the rules
-- are in config/permissions.php instead — notices: 'notices.wait_for_approval'.)
ALTER TABLE user_accounts
    DROP COLUMN review,
    DROP COLUMN review_note,
    DROP COLUMN reviewed_by,
    DROP COLUMN reviewed_at;
