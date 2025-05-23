<?php
namespace Edaha\Renderers;
use Edaha\Interfaces\RendererInterface;
use Edaha\Entities\Board;
use Edaha\Entities\Post;

class TextBoardRegenerator extends BoardRegenerator
{
    public function regenerateAll(): void
    {
        parent::regeneratePages();
        $this->regenerateAllThreadsPage();
    }

    public function regenerateAllThreadsPage(): void
    {  
        $this->twigData['isindex'] = false;
    
        $this->twigData['posts'] = $this->entityManager->getRepository(\Edaha\Entities\Post::class)
          ->getBoardRecentThreads($this->board->id, null);
        
        $content = \kxTemplate::get('board/' . $this->board->type . '/txt_all_threads', $this->twigData, true);
        
        \kxFunc::outputToFile(KX_BOARD . '/' . $this->board->directory . '/list.html', $content, $this->board->directory);
    }
}