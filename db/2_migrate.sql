-- Change to allow all board modules to load from sql
alter table
    `boards`
MODIFY
    `board_type` varchar(255) not null;

update
    `boards`
set
    board_type = 'image'
where
    board_id in (1, 2, 3, 4);

-- Add /application/board/modules/manage/recents
-- TODO We should be able to have our modules autoload without needing to be added to the database?
insert into
    `modules` (
        `module_name`,
        `module_application`,
        `module_file`,
        `module_description`,
        `module_position`,
        `module_manage`
    )
values
    (
        'Recents',
        'core',
        'recents',
        'Recent Posts and Images',
        0,
        1
    );

alter table
    `post_files`
add
    `file_reviewed` boolean default false;

alter table
    `post_files`
add
    constraint FK_post_files_posts foreign key (file_post) references posts (post_id) on delete cascade;

-- Resurrect /application/core/modules/lookfeel
insert into
    `modules` (
        `module_name`,
        `module_application`,
        `module_file`,
        `module_description`,
        `module_position`,
        `module_manage`
    )
values
    (
        'Look & Feel',
        'core',
        'lookfeel',
        'Look & Feel',
        0,
        1
    );

-- Add primary key and source module columns for modlog
alter table
    `modlog`
add
    column `id` int unsigned primary key auto_increment;

alter table
    `modlog`
add
    column `source_module` varchar(255);

-- Get reports working
alter table `reports`
    add primary key id,
    rename column `postid` to `post_id`;
    add column `board_id` smallint,
    add foreign key (board_id)
        references boards(board_id)
        on delete cascade
    drop column `board`
    rename column `when` to `timestamp`;

insert into
    `modules` (
        `module_name`,
        `module_application`,
        `module_file`,
        `module_description`,
        `module_position`,
        `module_manage`
    )
values
    (
        'Reports',
        'board',
        'reports',
        'Reports',
        0,
        1
    );

-- Improve bans functionality
alter table `banlist`
    drop column `type`,
    modify column `expired` 
        boolean default false,
    modify column `allow_read` 
        boolean default true,
    modify column `boards`
        json,
    drop column `by`,
    add column `created_by_staff_id` 
        smallint unsigned not null,
    add foreign key (created_by_staff_id)
        references staff(user_id);

-- Webp functionality
insert into
  `filetypes` (
    `type_ext`,
    `type_mime`,
    `type_force_thumb`
  )
values
  ('webp', 'image/webp', 1);

-- Update password hashing
alter table `staff`
    modify column `user_password`
        binary(60);