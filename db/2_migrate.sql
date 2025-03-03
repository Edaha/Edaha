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
    constraint FK_post_files_posts 
    foreign key (file_post) 
    references posts (post_id) on delete cascade;