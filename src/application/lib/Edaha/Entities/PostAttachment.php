<?php
namespace Edaha\Entities;

class PostAttachment
{
    public static function deleteFile($board_id, $file_name, &$db)
    {
        $file_type = $db->select("post_files")
            ->fields("post_files", ["file_type"])
            ->condition("file_board", $board_id)
            ->condition("file_name", $file_name)
            ->execute()
            ->fetchField();

        if (isset($file_type)) {
            // TODO This should come from the cache if available
            $board_name = $db->select("boards")
                ->fields("boards", ["board_name"])
                ->condition("board_id", $board_id)
                ->execute()
                ->fetchField();

            $file_paths['main']    = KX_BOARD . '/' . $board_name . '/src/' . $file_name . '.' . $file_type;
            $file_paths['thumb']   = KX_BOARD . '/' . $board_name . '/thumb/' . $file_name . 's.' . $file_type;
            $file_paths['catalog'] = KX_BOARD . '/' . $board_name . '/src/' . $file_name . 'c.' . $file_type;

            foreach ($file_paths as $path) {
                if (file_exists($path)) {
                    try {
                        unlink($path);
                    } catch (Exception $e) {
                        kxFunc::showError('Error when deleting file: ' . $e->getMessage());
                    }
                }
            }
        }

        $deleted = $db->delete("post_files")
            ->condition("file_board", $board_id)
            ->condition("file_name", $file_name)
            ->execute();

        return $deleted;
    }
}
