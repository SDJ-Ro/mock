-- Parent Approvals: a parent who says no to a club / sport may say why (optional; the club's teacher reads it later)
ALTER TABLE activity_members
    ADD COLUMN parent_note VARCHAR(255) NULL AFTER parent_answered_at;
