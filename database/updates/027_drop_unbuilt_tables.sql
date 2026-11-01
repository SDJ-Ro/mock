-- Tables made early for features whose backend isn't built yet; nothing reads them and they only hold demo rows.
-- Each one is made again, in the shape its feature needs, when that feature is built:
--   school_settings              → not planned (the school's details live in config/white_label/)
--   certificates                 → B-4 Character certificates
--   feedback                     → B-5 Feedback (needs an audience: student / parent / both)
--   complaints, complaint_messages → B-6 Complaints (one-way, not a message thread)
DROP TABLE IF EXISTS complaint_messages;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS certificates;
DROP TABLE IF EXISTS school_settings;
