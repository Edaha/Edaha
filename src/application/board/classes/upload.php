<?php
/*
 * This file is part of kusaba.
 *
 * kusaba is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * kusaba is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * kusaba; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * +------------------------------------------------------------------------------+
 * Upload class
 * +------------------------------------------------------------------------------+
 * Used for image/misc file upload through the post form on board/thread pages
 * +------------------------------------------------------------------------------+
 */

class Upload
{
  public $files = array();
  public $file_location = array();
  public $file_thumb_location = array();
  public $file_thumb_cat_location = array();
  public $isreply = false;
  public $isvideo = false;
  protected $environment;
  protected $db;
  protected $request;

  public function __construct(kxEnv $environment)
  {
    $this->environment = $environment;
    $this->db = kxDB::getInstance();
    $this->request = kxEnv::$request;
  }

  public function HandleUpload($postData, $boardData)
  {
    $file_name = isset($_FILES['imagefile']['name']) ? $_FILES['imagefile']['name'] : '';
    $file_size = isset($_FILES['imagefile']['size']) ? $_FILES['imagefile']['size'] : '';
    $file_type = isset($_FILES['imagefile']['type']) ? $_FILES['imagefile']['type'] : '';

    // TODO Finishg removing oekaki
    // if (!isset($postData['is_oekaki'])) {
    if ($file_name[0]) {
      if (count($file_name) > $boardData->board_max_files) {
        kxFunc::showError(sprintf(_('Please select only %d file(s) to upload.'), $boardData->board_max_files));
      }
      for ($i = 0; $i < $boardData->board_max_files; $i++) {
        if ($i == 1 && !$postData['is_reply']) {
          // Only 1 upload for an OP
          break;
        }
        if (!$file_name[$i]) {
          // Previous file was the last uploaded.
          break;
        }
        // Too big?
        if ($i == 0 && array_sum($file_size) > $boardData->board_max_upload_size) {
          kxFunc::showError(sprintf(_('Please make sure your total upload does not exceed %dB'), $boardData->board_max_upload_size));
        }
        // Do we have an error?
        switch ($_FILES['imagefile']['error'][$i]) {
          case UPLOAD_ERR_OK:
            break;
          case UPLOAD_ERR_INI_SIZE:
            kxFunc::showError(sprintf(_('The total upload exceeds the upload_max_filesize directive (%s) in php.ini.'), ini_get('upload_max_filesize')));
            break;
          case UPLOAD_ERR_FORM_SIZE:
            kxFunc::showError(sprintf(_('Please make sure your total upload does not exceed %dB'), $boardData->board_max_upload_size));
            break;
          case UPLOAD_ERR_PARTIAL:
            kxFunc::showError(_('The uploaded file was only partially uploaded.'));
            break;
          case UPLOAD_ERR_NO_FILE:
            kxFunc::showError(_('No file was uploaded.'));
            break;
          case UPLOAD_ERR_NO_TMP_DIR:
            kxFunc::showError(_('Missing a temporary folder.'));
            break;
          case UPLOAD_ERR_CANT_WRITE:
            kxFunc::showError(_('Failed to write file to disk'));
            break;
          default:
            kxFunc::showError(_('Unknown File Error'));
        }

        $this->files[$i]['file_is_special'] = false;
        $this->files[$i]['file_type'] = preg_replace('/.*(\..+)/', '\1', $file_name[$i]);
        $this->files[$i]['file_type'] = strtolower($this->files[$i]['file_type']);

        if ($this->files[$i]['file_type'] == '.jpeg') {
          /* Fix for the rarely used 4-char format */
          $this->files[$i]['file_type'] = '.jpg';
        }

        $this->files[$i]['file_size'] = $file_size[$i];

        $pass = true;
        // File not uploaded properly or no upload at all.
        if (!is_file($_FILES['imagefile']['tmp_name'][$i]) || !is_readable($_FILES['imagefile']['tmp_name'][$i])) {
          $pass = false;
        } else {
          if (in_array($this->files[$i]['file_type'], ['.jpg', '.gif', '.png', '.webp'])) {
            // Possible XSS attack with fake image
            if (!@getimagesize($_FILES['imagefile']['tmp_name'][$i])) {
              $pass = false;
            }
          }
        }
        if (!$pass) {
          kxFunc::showError(_('File transfer failure. Please go back and try again.'));
        }

        // Clean up our file name
        $this->files[$i]['file_name'] = substr(htmlspecialchars(preg_replace('/(.*)\..+/', '\1', $file_name[$i]), ENT_QUOTES), 0, 50);
        $this->files[$i]['original_file_name'] = $this->files[$i]['file_name'];
        // If we have file.ext.ext2, remove the preceeding dots because some improperly configured Apache servers will not read the file as they should
        $this->files[$i]['file_name'] = str_replace('.', '_', $this->files[$i]['file_name']);

        $this->files[$i]['file_md5'] = md5_file($_FILES['imagefile']['tmp_name'][$i]);
        // We save our MD5 files in a seperate array to check for duplicates in the same upload
        $filemd5s[$i] = $this->files[$i]['file_md5'];
        if (max(array_count_values($filemd5s)) > 1) {
          // Delete the now dead files if we have any.
          foreach ($this->file_location as $location) {
            unlink($location);
          }

          kxFunc::showError(_('Duplicate file entry detected.'));
        }

        // File already exists elsewhere?
        $exists_thread = kxFunc::checkMd5($this->files[$i]['file_md5'], $boardData->board_id);
        if (is_array($exists_thread)) {
          foreach ($this->file_location as $location) {
            unlink($location);
          }
          kxFunc::showError(_('Duplicate file entry detected.'), sprintf(_('Already posted %shere%s.'), '<a href="' . kxEnv::Get('kx:paths:boards:path') . '/' . $boardData->board_name . '/res/' . $exists_thread[0] . '.html#' . $exists_thread[1] . '">', '</a>'));
        }
        /* removed for now
        if (strtolower($this->files[$i]['file_type']) == 'svg') {
        require_once 'svg.class.php';
        $svg = new Svg($_FILES['imagefile']['tmp_name'][$i]);
        $this->files[$i]['image_w'] = $svg->width;
        $this->files[$i]['image_h'] = $svg->height;
        } else*/{
          $imageDim = getimagesize($_FILES['imagefile']['tmp_name'][$i]);
          $this->files[$i]['image_w'] = $imageDim[0];
          $this->files[$i]['image_h'] = $imageDim[1];
        }

        $filetype_forcethumb = $this->db->select("filetypes");
        $filetype_forcethumb->innerJoin("board_filetypes", "bf", "filetypes.type_id = bf.type_id");
        $filetype_forcethumb->innerJoin("boards", "b", "b.board_id = bf.board_id");
        $filetype_forcethumb = $filetype_forcethumb->fields("filetypes", array("type_force_thumb"))
          ->condition("filetypes.type_ext", substr($this->files[$i]['file_type'], 1))
          ->condition("board_name", $boardData->board_name)
          ->execute()
          ->fetchField();

        if (isset($filetype_forcethumb)) {
          if ($filetype_forcethumb == 1) {
            $this->files[$i]['file_name'] = time() . mt_rand(1, 99);
            $file_names[$i] = $this->files[$i]['file_name'];

            while (max(array_count_values($file_names)) > 1) {
              // In a multi-file upload, there's a small chance that we'll get the same name as another file in the batch, this should fix that in case that happens
              $this->files[$i]['file_name'] = time() . mt_rand(1, 99);
            }

            {
              $this->file_location[$i] = KX_BOARD . '/' . $boardData->board_name . '/src/' . $this->files[$i]['file_name'] . $this->files[$i]['file_type'];
              $this->file_thumb_location[$i] = KX_BOARD . '/' . $boardData->board_name . '/thumb/' . $this->files[$i]['file_name'] . 's' . $this->files[$i]['file_type'];
              $this->file_thumb_cat_location[$i] = KX_BOARD . '/' . $boardData->board_name . '/thumb/' . $this->files[$i]['file_name'] . 'c' . $this->files[$i]['file_type'];

              if (!move_uploaded_file($_FILES['imagefile']['tmp_name'][$i], $this->file_location[$i])) {
                kxFunc::showError(_('Could not copy uploaded image.'));
              }
              chmod($this->file_location[$i], 0644);

              if ($file_size[$i] == filesize($this->file_location[$i])) {
                if ((!$postData['is_reply'] && ($this->files[$i]['image_w'] > kxEnv::Get('kx:images:thumbw') || $this->files[$i]['image_h'] > kxEnv::Get('kx:images:thumbh'))) || ($postData['is_reply'] && ($this->files[$i]['image_w'] > kxEnv::Get('kx:images:replythumbw') || $this->files[$i]['image_h'] > kxEnv::Get('kx:images:replythumbh')))) {
                  if (!$postData['is_reply']) {
                    if (!$this->createThumbnail($this->file_location[$i], $this->file_thumb_location[$i], kxEnv::Get('kx:images:thumbw'), kxEnv::Get('kx:images:thumbh'))) {
                      kxFunc::showError(_('Could not create thumbnail.'));
                    }
                  } else {
                    if (!$this->createThumbnail($this->file_location[$i], $this->file_thumb_location[$i], kxEnv::Get('kx:images:replythumbw'), kxEnv::Get('kx:images:replythumbh'))) {
                      kxFunc::showError(_('Could not create thumbnail.'));
                    }
                  }
                } else {
                  if (!$this->createThumbnail($this->file_location[$i], $this->file_thumb_location[$i], $this->files[$i]['image_w'], $this->files[$i]['image_h'])) {
                    kxFunc::showError(_('Could not create thumbnail.'));
                  }
                }
                if (!$this->createThumbnail($this->file_location[$i], $this->file_thumb_cat_location[$i], kxEnv::Get('kx:images:catthumbw'), kxEnv::Get('kx:images:catthumbh'))) {
                  kxFunc::showError(_('Could not create thumbnail.'));
                }
                $imageDim_thumb = getimagesize($this->file_thumb_location[$i]);
                $this->files[$i]['thumb_w'] = $imageDim_thumb[0];
                $this->files[$i]['thumb_h'] = $imageDim_thumb[1];
              } else {
                kxFunc::showError(_('File was not fully uploaded. Please go back and try again.'));
              }
            }
          } else {
            /* Fetch the mime requirement for this special filetype */
            $filetype_required_mime = $this->db->select("filetypes")
              ->fields("filetypes", ["type_mime"])
              ->condition("type_ext", substr($this->files[$i]['file_type'], 1))
              ->execute()
              ->fetchField();
            // Filename cleanup.
            $this->files[$i]['file_name'] = htmlspecialchars_decode($this->files[$i]['file_name'], ENT_QUOTES);
            $this->files[$i]['file_name'] = stripslashes($this->files[$i]['file_name']);
            $this->files[$i]['file_name'] = str_replace("\x80", " ", $this->files[$i]['file_name']);
            $this->files[$i]['file_name'] = str_replace(' ', '_', $this->files[$i]['file_name']);
            $this->files[$i]['file_name'] = str_replace('#', '(number)', $this->files[$i]['file_name']);
            $this->files[$i]['file_name'] = str_replace('@', '(at)', $this->files[$i]['file_name']);
            $this->files[$i]['file_name'] = str_replace('/', '(fwslash)', $this->files[$i]['file_name']);
            $this->files[$i]['file_name'] = str_replace('\\', '(bkslash)', $this->files[$i]['file_name']);

            {
              $this->file_location[$i] = KX_BOARD . '/' . $boardData->board_name . '/src/' . $this->files[$i]['file_name'] . $this->files[$i]['file_type'];
              if (file_exists($this->file_location[$i])) {
                kxFunc::showError(_('A file by that name already exists'));
                die;
              }
              // MP3 files get special processing to grab their embedded image should they have one
              if ($this->files[$i]['file_type'] == '.mp3') {
                $getID3 = new getID3;
                $getID3->analyze($_FILES['imagefile']['tmp_name'][$i]);
                if (isset($getID3->info['id3v2']['APIC'][0]['data']) && isset($getID3->info['id3v2']['APIC'][0]['image_mime'])) {
                  $source_data = $getID3->info['id3v2']['APIC'][0]['data'];
                  $mime = $getID3->info['id3v2']['APIC'][0]['image_mime'];
                } elseif (isset($getID3->info['id3v2']['PIC'][0]['data']) && isset($getID3->info['id3v2']['PIC'][0]['image_mime'])) {
                  $source_data = $getID3->info['id3v2']['PIC'][0]['data'];
                  $mime = $getID3->info['id3v2']['PIC'][0]['image_mime'];
                }

                if ($source_data) {
                  $im = imagecreatefromstring($source_data);
                  if (preg_match("/png/", $mime)) {
                    $ext = ".png";
                    imagepng($im, $this->file_location[$i] . ".tmp", 0, PNG_ALL_FILTERS);
                  } else if (preg_match("/jpg|jpeg/", $mime)) {
                    $ext = ".jpg";
                    imagejpeg($im, $this->file_location[$i] . ".tmp");
                  } else if (preg_match("/gif/", $mime)) {
                    $ext = ".gif";
                    imagegif($im, $this->file_location[$i] . ".tmp");
                  }
                  $this->file_thumb_location[$i] = KX_BOARD . '/' . $boardData->board_name . '/thumb/' . $this->files[$i]['file_name'] . 's' . $ext;
                  if (!$postData['is_reply']) {
                    if (!$this->createThumbnail($this->file_location[$i] . ".tmp", $this->file_thumb_location[$i], kxEnv::Get('kx:images:thumbw'), kxEnv::Get('kx:images:thumbh'))) {
                      kxFunc::showError(_('Could not create thumbnail.'));
                    }
                  } else {
                    if (!$this->createThumbnail($this->file_location[$i] . ".tmp", $this->file_thumb_location[$i], kxEnv::Get('kx:images:replythumbw'), kxEnv::Get('kx:images:replythumbh'))) {
                      kxFunc::showError(_('Could not create thumbnail.'));
                    }
                  }
                  $imageDim_thumb = getimagesize($this->file_thumb_location[$i]);
                  $this->files[$i]['thumb_w'] = $imageDim_thumb[0];
                  $this->files[$i]['thumb_h'] = $imageDim_thumb[1];
                  unlink($this->file_location[$i] . ".tmp");
                }
              }

              // Move the file from the post data to the server
              if (!move_uploaded_file($_FILES['imagefile']['tmp_name'][$i], $this->file_location[$i])) {
                kxFunc::showError(_('Could not copy uploaded image.'));
              }

              /* Check if the filetype provided comes with a MIME restriction */
              if ($filetype_required_mime != '') {
                /* Check if the MIMEs don't match up */
                if (mime_content_type($this->file_location[$i]) != $filetype_required_mime) {
                  /* Delete the file we just uploaded and kill the script */
                  foreach ($this->file_location as $location) {
                    unlink($location);
                  }

                  kxFunc::showError(_('Invalid MIME type for this filetype.'));
                }
              }

              /* Make sure the entire file was uploaded */
              if ($file_size[$i] != filesize($this->file_location[$i])) {
                foreach ($this->file_location as $location) {
                  unlink($location);
                }

                kxFunc::showError(_('File transfer failure. Please go back and try again.'));
              }
            }
            /* Flag that the file used isn't an internally supported type */
            $this->files[$i]['file_is_special'] = true;
          }
        } else {
          kxFunc::showError(_('Sorry, that filetype is not allowed on this board.'));
        }

      }
    } elseif (isset($this->request['embed'])) {
      if ($this->request['embed'] != '') {
        $this->request['embed'] = strip_tags(substr($this->request['embed'], 0, 30));
        $video_id = $this->request['embed'];
        $this->files[0]['file_name'] = $video_id;

        if ($video_id != '' && strpos($video_id, '@') == false && strpos($video_id, '&') == false) {

          $embeds = $this->db->select("embeds")
            ->fields("embeds")
            ->execute()
            ->fetchAll();
          $worked = false;
          foreach ($embeds as $line) {
            if ((strtolower($this->request['embedtype']) == strtolower($line->embed_name)) && in_array($line->embed_ext, explode(',', $boardData->board_embeds_allowed))) {
              $worked = true;
              $videourl_start = $line->embed_url;
              $this->files[0]['file_type'] = '.' . strtolower($line->embed_ext);
            }
          }

          if (!$worked) {
            kxFunc::showError(_('Invalid video type.'));
          }

          $results = $this->db->select("post_files")
            ->fields("post_files")
            ->innerJoin("posts", "", "post_board = file_board AND post_id = file_post")
            ->condition("file_board", $boardData->board_id)
            ->condition("file_name", $video_id)
            ->condition("post_deleted", 0)
            ->countQuery()
            ->execute()
            ->fetch();
          if (!$results) {
            $video_check = kxFunc::check_link($videourl_start . $video_id);
            switch ($video_check[1]) {
              case 404:
                kxFunc::showError(_('Unable to connect to') . ': ' . $videourl_start . $video_id);
                break;
              case 303:
                kxFunc::showError(_('Invalid video ID.'));
                break;
              case 302:
                // Continue
                break;
              case 301:
                // Continue
                break;
              case 200:
                // Continue
                break;
              default:
                kxFunc::showError(_('Invalid response code ') . ':' . $video_check[1]);
                break;
            }
            $this->isvideo = true;
          } else {
            $results = $this->db->select("post_files");
            $results->innerJoin("posts", "", "post_board = file_board AND post_id = file_post");
            $results = $results->fields("posts", array("post_id", "post_parent"))
              ->condition("file_board", $boardData->board_id)
              ->condition("file_name", $video_id)
              ->condition("post_deleted", 0)
              ->range(0, 1)
              ->execute()
              ->fetchAll();

            foreach ($results as $line) {
              $real_threadid = ($line->post_parent == 0) ? $line->post_id : $line->post_parent;
              kxFunc::showError(sprintf(_('That video ID has already been posted %shere%s.'), '<a href="' . kxEnv::Get('kx:paths:boards:folder') . '/' . $boardData->board_id . '/res/' . $real_threadid . '.html#' . $line->post_parent . '">', '</a>'));
            }
          }
        } else {
          kxFunc::showError(_('Invalid ID'));
        }
      }
    }

    // TODO Finish removing oekaki
    /*
  } else {
  $this->files[0]['file_name'] = time() . mt_rand(1, 99);
  $this->files[0]['original_file_name'] = $this->files[0]['file_name'];
  $this->files[0]['file_md5'] = md5_file($postData['oekaki']);
  $this->files[0]['file_type'] = '.png';
  $this->files[0]['file_size'] = filesize($postData['oekaki']);
  $imageDim = getimagesize($postData['oekaki']);
  $this->files[0]['image_w'] = $imageDim[0];
  $this->files[0]['image_h'] = $imageDim[1];

  if (!copy($postData['oekaki'], KX_BOARD . '/' . $boardData->board_name . '/src/' . $this->files[0]['file_name'] . $this->files[0]['file_type'])) {
  kxFunc::showError(_('Could not copy uploaded image.'));
  }

  $oeakaki_animation = substr($postData['oekaki'], 0, -4) . '.pch';
  if (file_exists($oeakaki_animation)) {
  if (!copy($oeakaki_animation, KX_BOARD . '/' . $boardData->board_name . '/src/' . $this->files[0]['file_name'] . '.pch')) {
  kxFunc::showError(_('Could not copy animation.'));
  }
  unlink($oeakaki_animation);
  }

  $thumbpath = KX_BOARD . '/' . $boardData->board_name . '/thumb/' . $this->files[0]['file_name'] . 's' . $this->files[0]['file_type'];
  $thumbpath_cat = KX_BOARD . '/' . $boardData->board_name . '/thumb/' . $this->files[0]['file_name'] . 'c' . $this->files[0]['file_type'];
  if (
  (!$postData['is_reply'] && ($this->files[0]['image_w'] > kxEnv::Get('kx:images:thumbw') || $this->files[0]['image_h'] > kxEnv::Get('kx:images:thumbh'))) ||
  ($postData['is_reply'] && ($this->files[0]['image_w'] > kxEnv::Get('kx:images:replythumbw') || $this->files[0]['image_h'] > kxEnv::Get('kx:images:replythumbh')))
  ) {
  if (!$postData['is_reply']) {
  if (!$this->createThumbnail($postData['oekaki'], $thumbpath, kxEnv::Get('kx:images:thumbw'), kxEnv::Get('kx:images:thumbh'))) {
  kxFunc::showError(_('Could not create thumbnail.'));
  }
  } else {
  if (!$this->createThumbnail($postData['oekaki'], $thumbpath, kxEnv::Get('kx:images:replythumbw'), kxEnv::Get('kx:images:replythumbh'))) {
  kxFunc::showError(_('Could not create thumbnail.'));
  }
  }
  } else {
  if (!$this->createThumbnail($postData['oekaki'], $thumbpath, $this->files[0]['image_w'], $this->files[0]['image_h'])) {
  kxFunc::showError(_('Could not create thumbnail.'));
  }
  }
  if (!$this->createThumbnail($postData['oekaki'], $thumbpath_cat, kxEnv::Get('kx:images:catthumbw'), kxEnv::Get('kx:images:catthumbh'))) {
  kxFunc::showError(_('Could not create thumbnail.'));
  }

  $imgDim_thumb = getimagesize($thumbpath);
  $this->files[0]['thumb_w'] = $imgDim_thumb[0];
  $this->files[0]['thumb_h'] = $imgDim_thumb[1];
  unlink($postData['oekaki']);
  }*/
  }
  /* Image handling */
  /**
   * Create a thumbnail
   *
   * @param string $source File to be thumbnailed
   * @param string $destination Path to place the thumbnail
   * @param integer $new_w Maximum width
   * @param integer $new_h Maximum height
   * @return boolean Success/fail
   */
  public function createThumbnail($source, $destination, $new_w, $new_h)
  {
    if (kxEnv::Get('kx:images:method') == 'imagemagick') {
      $convert = 'convert ' . escapeshellarg($source);
      if (!kxEnv::Get('kx:images:animated')) {
        $convert .= '[0] ';
      }
      $convert .= ' -resize ' . $new_w . 'x' . $new_h . ' -quality ';
      if (substr($destination, 0, -3) != 'gif') {
        $convert .= '70';
      } else {
        $convert .= '90';
      }
      $convert .= ' ' . escapeshellarg($destination);
      exec($convert);

      if (is_file($destination)) {
        return true;
      } else {
        return false;
      }
    } elseif (kxEnv::Get('kx:images:method') == 'gd') {
      $system = explode(".", $destination);
      $system = array_reverse($system);
      
      $src_img = imagecreatefromstring(file_get_contents($source));
      if (!$src_img) {
        kxFunc::showError(_('Unable to read uploaded file during thumbnailing.'), _('A common cause for this is an incorrect extension when the file is actually of a different type.'));
      }
      $old_x = imageSX($src_img);
      $old_y = imageSY($src_img);
      if ($old_x > $old_y) {
        $percent = $new_w / $old_x;
      } else {
        $percent = $new_h / $old_y;
      }
      $thumb_w = round($old_x * $percent);
      $thumb_h = round($old_y * $percent);

      $dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
      $this->fastImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y, $system);

      if (function_exists('imagewebp')) {
        // If we have webp support, let's thumbnail as .webp to save bandwidth
        if (!imagewebp($dst_img, $destination)) {
          echo 'unable to imagewebp.';
          return false;
        }
      } else {
        if (preg_match("/png/", $system[0])) {
          if (!imagepng($dst_img, $destination, 0, PNG_ALL_FILTERS)) {
            echo 'unable to imagepng.';
            return false;
          }
        } else if (preg_match("/jpg|jpeg/", $system[0])) {
          if (!imagejpeg($dst_img, $destination, 70)) {
            echo 'unable to imagejpg.';
            return false;
          }
        } else if (preg_match("/gif/", $system[0])) {
          if (!imagegif($dst_img, $destination)) {
            echo 'unable to imagegif.';
            return false;
          }
        }
      }

      imagedestroy($dst_img);
      imagedestroy($src_img);

      return true;
    }

    return false;
  }

  /* Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable. */
  /**
   * Faster method than only calling imagecopyresampled()
   *
   * @return boolean Success/fail
   */
  public function fastImageCopyResampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $system, $quality = 3)
  {
    /*
    Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5.
    1 = Up to 600 times faster. Poor results, just uses imagecopyresized but removes black edges.
    2 = Up to 95 times faster. Images may appear too sharp, some people may prefer it.
    3 = Up to 60 times faster. Will give high quality smooth results very close to imagecopyresampled.
    4 = Up to 25 times faster. Almost identical to imagecopyresampled for most images.
    5 = No speedup. Just uses imagecopyresampled, highest quality but no advantage over imagecopyresampled.
     */

    if (empty($src_image) || empty($dst_image) || $quality <= 0) {return false;}

    if (preg_match("/png/", $system[0]) || preg_match("/gif/", $system[0])) {
      $colorcount = imagecolorstotal($src_image);
      if ($colorcount <= 256 && $colorcount != 0) {
        imagetruecolortopalette($dst_image, true, $colorcount);
        imagepalettecopy($dst_image, $src_image);
        $transparentcolor = imagecolortransparent($src_image);
        imagefill($dst_image, 0, 0, $transparentcolor);
        imagecolortransparent($dst_image, $transparentcolor);
      } else {
        imageAlphaBlending($dst_image, false);
        imageSaveAlpha($dst_image, true); //If the image has Alpha blending, lets save it
      }
    }

    if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
      $temp = imagecreatetruecolor($dst_w * $quality + 1, $dst_h * $quality + 1);
      if (preg_match("/png/", $system[0])) {
        $background = imagecolorallocate($temp, 0, 0, 0);
        ImageColorTransparent($temp, $background); // make the new temp image all transparent
        imagealphablending($temp, false); // turn off the alpha blending to keep the alpha channel
      }
      imagecopyresized($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
      imagecopyresampled($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
      imagedestroy($temp);
    } else {
      imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    }

    return true;
  }
}
