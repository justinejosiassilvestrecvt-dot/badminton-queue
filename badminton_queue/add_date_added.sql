USE badminton_queue;
-- Run this ONLY if you already imported the old schema.sql (adds date_added to both tables)
ALTER TABLE courts        ADD COLUMN date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status;
ALTER TABLE queue_entries ADD COLUMN date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status;
