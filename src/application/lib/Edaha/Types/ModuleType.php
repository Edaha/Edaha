<?php
namespace Edaha\Types;

enum ModuleType: string
{
    case Core = 'core';
    case BoardType = 'board';
    case IndexType = 'index';
    case SiteType = 'site';
    case BoardRegenerator = 'board_regenerator';
    case BoardRenderer = 'board_renderer';
    case PostPreProcessor = 'post_preprocessor';
    case PostProcessor = 'post_processor';
    case PostPostProcessor = 'post_postprocessor';

    public function toString(): string {
        return $this->value;
    }
}