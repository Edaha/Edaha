<?php
use Edaha\Entities\Board;
use Edaha\Entities\Section;
use Edaha\Entities\Post;
/*
 * Section for building the news page
 * Last Updated: $Date$

 * @author 		$Author$

 * @package		kusaba
 * @subpackage	core

 * @version		$Revision$
 *
 */

if (! defined('KUSABA_RUNNING')) {
    print "<h1>Access denied</h1>You cannot access this file directly.";
    die();
}

class public_core_index_news extends kxCmd
{

    /**
     * Arguments eventually being sent to twig
     *
     * @var Array()
     */
    protected $twigData;

    public function exec(kxEnv $environment)
    {
        if (isset($this->request['view'])) {
            switch ($this->request['view']) {
                case 'faq':
                    $type = 1;
                    break;
                case 'rules':
                    $type = 2;
                    break;
            }
        } else {
            $this->request['view'] = 'news';
            $type                  = 0;
        }
        $this->twigData['styles'] = explode(':', kxEnv::Get('kx:css:sitestyles'));
        
        $front_board = $this->entityManager->getRepository(Board::class)
            ->findOneBy(['directory' => 'frontpage_' . $this->request['view']]);
        if (!$front_board) {
            die();
        }

        $posts = $this->entityManager->getRepository(Post::class)
            ->getBoardAllThreads($front_board->id);
        $this->twigData['entries'] = $posts;
        // TODO Order posts depending on the board
        // TODO Paginate

        $this->twigData['pages'] = count($posts) / 2;

        // Get all visible sections
        // TODO Section order
        $sections = $this->entityManager->getRepository(Section::class)
            ->findBy(['is_hidden' => false]);
        if (!$sections) {
            $sections = [];
        }
        $this->twigData['sections'] = $sections;

        // Get recent posts
        $recentposts = $this->entityManager->getRepository(Post::class)
            ->getAllRecentPosts(6);
        if (!$recentposts) {
            $recentposts = [];
        }
        $this->twigData['recentposts'] = $recentposts;

        // TODO Get recent images/attachments
        $images = [];
        $this->twigData['recentimages'] = $images;

        kxTemplate::output("index", $this->twigData);
    }
}
