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
alter table
    `reports`
add
    primary key id,
    rename column `postid` to `post_id`;

add
    column `board_id` smallint,
add
    foreign key (board_id) references boards(board_id) on delete cascade drop column `board` rename column `when` to `timestamp`;

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
alter table
    `banlist` drop column `type`,
modify
    column `expired` boolean default false,
modify
    column `allow_read` boolean default true,
modify
    column `boards` json,
    drop column `by`,
add
    column `created_by_staff_id` smallint unsigned not null,
add
    foreign key (created_by_staff_id) references staff(user_id);

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
alter table
    `staff`
modify
    column `user_password` binary(60);

-- Clean up `posts` db model
-- Timestamp fields to mysql timestamp
alter table
    posts
modify
    column post_timestamp bigint,
modify
    column post_delete_time bigint,
modify
    column post_bumped bigint;

update
    posts
set
    post_timestamp = cast(from_unixtime(post_timestamp) as unsigned);

update
    posts
set
    post_delete_time = cast(from_unixtime(post_delete_time) as unsigned);

update
    posts
set
    post_bumped = cast(from_unixtime(post_bumped) as unsigned);

alter table
    posts
modify
    column post_timestamp timestamp not null default current_timestamp,
modify
    column post_delete_time timestamp,
modify
    column post_bumped timestamp;

-- Boolean fields to boolean
alter table
    posts
modify
    column post_stickied boolean default false,
modify
    column post_locked boolean default false,
modify
    column post_reviewed boolean default false,
modify
    column post_deleted boolean default false;

-- Clean up column names
alter table
    posts 
    rename column post_board to board_id,
    rename column post_parent to parent_post_id,
    rename column post_name to name,
    rename column post_tripcode to tripcode,
    rename column post_email to email,
    rename column post_subject to subject,
    rename column post_message to message,
    rename column post_password to password,
    rename column post_ip to ip,
    rename column post_ip_md5 to ip_md5,
    rename column post_tag to tag,
    rename column post_timestamp to created_at_timestamp,
    rename column post_stickied to is_stickied,
    rename column post_locked to is_locked,
    rename column post_authority to authority,
    rename column post_reviewed to is_reviewed,
    rename column post_delete_time to deleted_at_timestamp,
    rename column post_deleted to is_deleted,
    rename column post_bumped to bumped_at_timestamp;
