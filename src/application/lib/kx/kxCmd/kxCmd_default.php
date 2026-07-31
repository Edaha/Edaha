<?php

namespace kx\kxCmd;

use kx\kxEnv;

/**
 * kxCmd_default
 * For if we don't have a valid command, just load the index.
 */
class kxCmd_default extends kxCmd
{
    /**
     * Do execute method.
     *
     * @param	object	kxCmd reference
     */
    protected function exec(kxEnv $environment)
    {
        @header('Location: '.kxEnv::Get('kx:paths:main:path').kxEnv::Get('kx:paths:main:folder'));
    }
}
