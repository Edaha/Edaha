<?php
namespace Edaha\Types;

enum ModuleType: string
{
    /* General ideas:
    Core: Provide routing functions
    Board: Implement different types of boards (image, text, etc)
    Index: Handle front page features (news, faq, etc)
    Site: Manage site-wide configuration
    Board Renderer: Render board pages (e.g., for different board types)
    Post Preprocessor: Process posts before they are saved (e.g., filtering, formatting)
    Post Processor: Process posts after they are saved (e.g., generating thumbnails)
    Post Postprocessor: Additional processing after the main post processing (e.g., logging, notifications
    */
    case Core = 'core'; // Examples: index.php, post.php, manage.php
    case BoardType = 'board'; // ImageBoard, TextBoard, OekakiBoard, etc.
    case BoardRenderer = 'board_renderer';
    case PostPreProcessor = 'post_preprocessor';
    case PostProcessor = 'post_processor';
    case PostPostProcessor = 'post_postprocessor';

    public function toString(): string {
        return $this->value;
    }
}