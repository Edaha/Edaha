ALTER TABLE `boards` MODIFY `board_type` varchar(255) NOT NULL;
UPDATE `boards` SET board_type = 'image' WHERE board_id IN (1, 2, 3, 4);
