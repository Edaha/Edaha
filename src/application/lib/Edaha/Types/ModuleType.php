<?php
namespace Edaha\Types;

enum ModuleType: string
{
    case Core = 'core';
    case BoardType = 'board';
    case BoardRegenerator = 'board_regenerator';
    case BoardRenderer = 'board_renderer';
}