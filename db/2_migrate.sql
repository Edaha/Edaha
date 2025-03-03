ALTER TABLE `boards` MODIFY `board_type` varchar(255) NOT NULL;
UPDATE `boards` SET board_type = 'image' WHERE board_id IN (1, 2, 3, 4);

-- TODO We should be able to have our modules autoload without needing to be added to the database?
INSERT INTO `modules` (`module_name`, `module_application`, `module_file`, `module_description`, `module_position`, `module_manage`) VALUES
('Recents', 'core', 'recents', 'Recent Posts and Images', 0, 1);