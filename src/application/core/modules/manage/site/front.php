<?php
/**
 * Edaha - Site Management Front Controller
 *
 * Handles front page management actions (news, FAQ, rules) for the admin panel.
 * Supports creating, editing, and deleting entries via the management interface.
 *
 * @package   Edaha
 * @category  Modules
 * @module    manage_core_site_front
 */

use Edaha\Entities\Board;
use Edaha\Entities\Section;
use Edaha\Entities\Post;

class manage_core_site_front extends kxCmd
{
    /**
     * Arguments eventually being sent to twig
     *
     * @var Array()
     */
    protected $twigData;

    protected ?Board $board = null;

    public function exec(kxEnv $environment)
    {
        if (isset($this->request['board_id'])) {
            $this->board = $this->entityManager->getRepository(Board::class)->find($this->request['board_id']);
            if (! $this->board) {
                throw new Exception('Board not found');
            }
            $this->twigData['board'] = $this->board;
        }

        switch ($this->request['action']) {
            case 'post':
                $this->_post();
                break;
            case 'edit':
                $this->_edit();
                break;
            case 'del':
                $this->_del();
                break;
        }
        switch ($this->request['do']) {
            case 'news':
            default:
                $this->_news();
                break;
            case 'faq':
                $this->_faq();
                break;
            case 'rules':
                $this->_rules();
                break;
            case 'frontpage':
                $this->_frontpage();
                break;
            case 'frontpage_manage':
                $this->_frontpage_manage();
                break;
        }
    }

    private function __install()
    {
        $frontpage_section = $this->entityManager->getRepository(Section::class)->findOneBy(['name' => 'Frontpage']);
        if (! $frontpage_section) {
            $frontpage_section = new Section('Frontpage', false);
            $this->entityManager->persist($frontpage_section);

            // TODO Flexible Frontpage Boards
            $news_board = $this->entityManager->getRepository(Board::class)->findOneBy(['name' => 'News']);
            if (! $news_board) {
                $news_board = new Board('News', 'frontpage_news');
                $this->entityManager->persist($news_board);
            }
            $news_board->is_locked = true;

            $faq_board = $this->entityManager->getRepository(Board::class)->findOneBy(['name' => 'FAQ']);
            if (! $faq_board) {
                $faq_board = new Board('FAQ', 'frontpage_faq');
                $this->entityManager->persist($faq_board);
            }
            $faq_board->is_locked = true;

            $rules_board = $this->entityManager->getRepository(Board::class)->findOneBy(['name' => 'Rules']);
            if (! $rules_board) {
                $rules_board = new Board('Rules', 'frontpage_rules');
                $this->entityManager->persist($rules_board);
            }
            $rules_board->is_locked = true;

            $frontpage_section->addBoard($news_board);
            $frontpage_section->addBoard($faq_board);
            $frontpage_section->addBoard($rules_board);

            $this->entityManager->flush();
        }
    }

    private function _frontpage()
    {
        $frontpage_section = $this->entityManager->getRepository(Section::class)->findOneBy(['name' => 'Frontpage']);
        if (! $frontpage_section) {
            $this->__install();
            $frontpage_section = $this->entityManager->getRepository(Section::class)->findOneBy(['name' => 'Frontpage']);
        }

        $this->twigData['frontpage_section'] = $frontpage_section;

        kxTemplate::output("manage/frontpage", $this->twigData);
    }

    private function _frontpage_manage()
    {
        kxTemplate::output("manage/frontpage_manage", $this->twigData);
    }

    private function _news()
    {
        $this->twigData['entries'] = $this->db->select("front")
            ->fields("front")
            ->condition("entry_type", 0)
            ->orderBy("entry_time", "DESC")
            ->execute()
            ->fetchAll();
        kxTemplate::output("manage/news", $this->twigData);
    }

    private function _faq()
    {
        $this->twigData['entries'] = $this->db->select("front")
            ->fields("front")
            ->condition("entry_type", 1)
            ->orderBy("entry_order", "ASC")
            ->execute()
            ->fetchAll();
        kxTemplate::output("manage/faq", $this->twigData);
    }

    private function _rules()
    {
        $this->twigData['entries'] = $this->db->select("front")
            ->fields("front")
            ->condition("entry_type", 2)
            ->orderBy("entry_order", "ASC")
            ->execute()
            ->fetchAll();
        kxTemplate::output("manage/rules", $this->twigData);
    }

    private function _post()
    {
        // Handles posting of front page content
        kxForm::addRule('subject', 'required')
            ->addRule('message', 'required')
            ->addRule('board_id', 'numeric')
            ->check();

        if (isset($this->request['edit']) && $this->request['edit'] != '') {
            $post = $this->entityManager->getRepository(Post::class)->find($this->request['edit']);
            if (! $post) {
                throw new Exception('Post not found');
            }
            $post->message = $this->request['message'];
            $post->subject = $this->request['subject'];
        } else {
            $post = New Post(
                $this->board,
                $this->request['message'],
                $this->request['subject']
            );
        }
        
        $post->poster->name = kxFunc::getManageUser()['user_name'];

        if (isset($this->request['email'])) {
            $post->poster->email = $this->request['email'];
        }

        // TODO Post ordering
        
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        if ($this->request['edit'] == "") {
            // New post
            $this->twigData['notice']['message'] = _('Entry successfully added.');
            logging::addLogEntry(
                kxFunc::getManageUser()['user_name'],
                sprintf('Created new post on %s', $this->board->name),
                __CLASS__
            );
        } else {
            // Update post
            $this->twigData['notice']['message'] = _('Entry successfully edited.');
            logging::addLogEntry(
                kxFunc::getManageUser()['user_name'],
                sprintf('Edited entry post %s on %s', $post->id, $this->board->name),
                __CLASS__
            );
        }
        $this->twigData['notice']['type'] = 'success';
    }

    private function _edit()
    {
        $entry = $this->entityManager->getRepository(Post::class)
            ->find($this->request['id']);
        
        $this->twigData['entry'] = $entry;
    }

    private function _del()
    {
        $post = $this->entityManager->getRepository(Post::class)->find($this->request['id']);
        if (! $post) {
            throw new Exception('Post not found');
        }
        $this->entityManager->remove($post);
        $this->entityManager->flush();

        $this->twigData['notice']['type']    = 'success';
        $this->twigData['notice']['message'] = _('Entry successfully deleted.');
        logging::addLogEntry(
            kxFunc::getManageUser()['user_name'],
            sprintf('Deleted %s entry', $this->request['do']),
            __CLASS__
        );
    }

}
