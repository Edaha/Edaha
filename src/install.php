<?php
DEFINE('IN_MANAGE', false);
include "init.php";

use Edaha\Entities\Module;
use Edaha\Types\ModuleType;
use Edaha\Entities\Board;
use Edaha\Entities\Section;

$em = kxOrm::getEntityManager();

if (isset($_POST['install'])) {
    installModules($em);
    addBoards($em);
    echo "Installation completed successfully.";
} else {
    echo '<form method="post">
            <input type="hidden" name="install" value="1">
            <button type="submit">Install</button>
          </form>';
}

function installModule($em, $moduleName, $moduleType, $moduleClass, $moduleDescription) {
    // Check if the module already exists
    $existingModule = $em->getRepository(Module::class)->findOneBy(['name' => $moduleName]);
    if ($existingModule) {
        echo "Module '$moduleName' already exists. Skipping installation.<br>";
        return;
    }

    $module = new Module(
        $moduleName,
        ModuleType::from($moduleType),
        $moduleClass,
        $moduleDescription,
        false // Assuming is_manage is false for installation
    );

    try {
        $em->persist($module);
        $em->flush();
        echo "Module '$moduleName' installed successfully.<br>";
    } catch (\Exception $e) {
        echo "Error installing module '$moduleName': " . $e->getMessage() . "<br>";
    }
}

function installModules($em) {
    installModule($em, 'Staff', 'core', 'staff', 'Staff Configuration');
    installModule($em, 'Image Board', 'board', 'image', 'Generator for an image-type board');
    installModule($em, 'Oekaki Board', 'board', 'oekaki', 'Generator for an oekaki-type board');
    installModule($em, 'Upload Board', 'board', 'upload', 'Generator for an upload-type board');
    installModule($em, 'Text Board', 'board', 'text', 'Generator for a text board');
    installModule($em, 'Site', 'core', 'site', 'Manage the Site Configuration');
    installModule($em, 'Index', 'core', 'index', 'Handles the front page features (news, faq, etc)');
    installModule($em, 'Bans', 'core', 'bans', 'Provides functionality for adding and editing bans.');
    installModule($em, 'Board', 'board', 'board', 'Allows setting of board options');
    installModule($em, 'Filters', 'board', 'filter', 'Provides filtering options');
    installModule($em, 'Attachment Options', 'board', 'attachments', 'Provides tools for adding, editing, and removing available post attachments.');
}

function addBoards($em) {
    $boards = [
        ['name' => 'Image Board', 'directory' => 'image'],
        ['name' => 'Text Board', 'directory' => 'text']
    ];

    foreach ($boards as $boardData) {
        // Check if the board already exists
        $existingBoard = $em->getRepository(Board::class)->findOneBy(['name' => $boardData['name']]);
        if ($existingBoard) {
            echo "Board '" . $boardData['name'] . "' already exists. Skipping.<br>";
            continue;
        }
        echo "Adding board: " . $boardData['name'] . "<br>";
        $board = new Board($boardData['name'], $boardData['directory']);
        $em->persist($board);
    }
    $em->flush();
}
