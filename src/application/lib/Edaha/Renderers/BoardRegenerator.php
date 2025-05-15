<?php
namespace Edaha\Renderers;
use Edaha\Interfaces\RegeneratorInterface;
use Edaha\Entities\Board;
use Edaha\Entities\BoardOption;
use Edaha\Entities\Post;

class BoardRegenerator implements RegeneratorInterface
{
    protected Board $board;
    protected $entityManager;

    protected array $twigData = [];

    public function __construct(Board $board, $entityManager)
    {
        $this->board = $board;
        $this->entityManager = $entityManager;
    }

    private function makeBoardFolders(string $directory): void
    {
        @mkdir($directory, 0777, true);
        @mkdir($directory . '/src/', 0777, true);
        @mkdir($directory . '/thumb/', 0777, true);
        @mkdir($directory . '/res/', 0777, true);
    }

    /**
     * Build the page header
     *
     * @param integer $replythread The ID of the thread the header is being build for.  0 if it is for a board page
     */
    protected function pageHeader($replythread = 0)
    {
      $this->twigData['title'] = '';
  
      if (\kxEnv::Get('kx:pages:dirtitle')) {
        $this->twigData['title'] .= '/' . $this->board->directory . '/ - ';
      }
      $this->twigData['title'] .= $this->board->name;
  
      $this->twigData['htmloptions'] = ((\kxEnv::Get('kx:misc:locale') == 'he' && empty($this->board->locale)) || $this->board->locale == 'he') ? ' dir="rtl"' : '';
      $this->twigData['locale'] = $this->board->locale;
      $this->twigData['board'] = $this->board;
  
      $this->twigData['boardlist'] = \kxFunc::visibleBoardList();
      $this->twigData['replythread'] = $replythread;
    }

    /**
     * Generate the postbox area
     *
     * @param integer $replythread The ID of the thread being replied to.  0 if not replying
     * @param string $postboxnotice The postbox notice
     * @return string The generated postbox
     */
    protected function postBox($replythread = 0)
    {
      if (\kxEnv::Get('kx:extras:blotter')) {
        $this->twigData['blotter'] = \kxFunc::getBlotter();
        $this->twigData['blotter_updated'] = \kxFunc::getBlotterLastUpdated();
      }
    }

    /**
     * Display the page footer
     *
     * @param boolean $noboardlist Force the board list to not be displayed
     * @param string $executiontime The time it took the page to be created
     * @param boolean $hide_extra Hide extra footer information, and display the manage link
     */
    protected function footer($noboardlist = false, $executiontime = 0, $hide_extra = false)
    {
        if ($noboardlist || $hide_extra) {
            $this->twigData['boardlist'] = "";
        }

        if ($executiontime) {
            $this->twigData['executiontime'] = round($executiontime, 2);
        }
    }

    public function regenerateAllPages(int $posts_per_page = 10): void
    {
        $this->twigData['board'] = $this->board;
        $this->twigData['file_path'] = KX_BOARD . '/' . $this->board->directory;

        $this->makeBoardFolders($this->twigData['file_path']);
        $this->pageHeader(0);
        $this->postBox(0);

        $board_options = $this->entityManager->getRepository(BoardOption::class)
            ->getOptionsByBoard($this->board->id);
        foreach ($board_options as $option) {
            $this->twigData['board_options'][$option['name']] = $option['value'];
        }

        $count_of_posts = count($this->board->posts);
        $count_of_pages = \kxFunc::pageCount($this->board->type, ($count_of_posts - 1)) - 1;

        // If no posts, $totalpages==-2, which causes the board to not regen.
        if ($count_of_pages < 0) {
            $count_of_pages = 0;
        }

        $this->twigData['count_of_pages'] = $count_of_pages;

        $i = 0;
        while ($i <= $count_of_pages) {
            $this->regeneratePage($i, $posts_per_page);
            $i++;
        }
    }

    public function regeneratePage(int $page, int $posts_per_page = 10): void
    {
        $executiontime_start_page = microtime(true);

        $this->twigData['current_page'] = $page;

        $threads = $this->entityManager->getRepository(\Edaha\Entities\Post::class)
            ->getBoardPaginatedThreads($this->board->id, $page, $posts_per_page);

        $this->twigData['threads'] = $threads;

        $this->footer(false, (microtime(true) - $executiontime_start_page));

        $content = \kxTemplate::get('board/' . $this->board->type . '/board_page', $this->twigData, true);

        if ($page == 0) {
            $page = KX_BOARD . '/' . $this->board->directory . '/' . \kxEnv::Get('kx:pages:first');
        } else {
            $page = KX_BOARD . '/' . $this->board->directory . '/' . $page . '.html';
        }
        
        \kxFunc::outputToFile($page, $content, $this->board->directory);
    }

    public function regenerateAllThreads(): void
    {
        $threads = $this->entityManager->getRepository(\Edaha\Entities\Post::class)
            ->getBoardAllThreads($this->board->id, null);
        
        foreach ($threads as $thread) {
            $this->regenerateThread($thread);
        }
    }

    public function regenerateThread(Post $thread): void
    {
        $executiontime_start_thread = microtime(true);

        $this->twigData['board'] = $thread->board;
        
        $this->pageHeader($thread->id);
        $this->postBox($thread->id);
        //-----------
        // Dwoo-hoo
        //-----------
        $this->twigData['replythread'] = $thread->id;
        $this->twigData['threadid'] = $thread->id;
        $this->twigData['thread'] = $thread;
        $this->twigData['replycount'] = count($thread->replies) - 1;
        $this->footer(false, (microtime(true) - $executiontime_start_thread));

        $content = \kxTemplate::get('board/' . $this->board->type . '/thread', $this->twigData, true);
        \kxFunc::outputToFile(KX_BOARD . '/' . $this->board->directory . '/res/' . $thread->id . '.html', $content, $this->board->directory);

        if (\kxEnv::Get('kx:extras:firstlast')) {
            $this->regenerateThreadPageFirst100($thread);
            $this->regenerateThreadPageLast50($thread);
        }
    }

    protected function regenerateThreadPageFirst100(Post $thread): void {
        if (count($thread->posts) < 100) {
            return;
        }
        $lastBit = "-100";
        $this->twigData['modifier'] = "first100";

        $content = \kxTemplate::get('board/' . $this->board->type . '/thread', $this->twigData, true);
        \kxFunc::outputToFile(KX_BOARD . '/' . $this->board->directory . $this->archive_dir . '/res/' . $thread->id . $lastBit . '.html', $content, $this->board->directory);
    }

    protected function regenerateThreadPageLast50(Post $thread): void {
        if (count($thread->posts) < 50) {
            return;
        }
        $lastBit = "+50";
        $this->twigData['modifier'] = "last50";

        $content = \kxTemplate::get('board/' . $this->board->type . '/thread', $this->twigData, true);
        \kxFunc::outputToFile(KX_BOARD . '/' . $this->board->directory . $this->archive_dir . '/res/' . $thread->id . $lastBit . '.html', $content, $this->board->directory);
    }

    public function regenerateAll(): void
    {
        $this->regenerateAllPages($this->board);
        $this->regenerateAllThreads($this->board);
    }

}