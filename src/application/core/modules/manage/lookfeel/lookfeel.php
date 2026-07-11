<?php

class manage_core_lookfeel_lookfeel extends kxCmd
{
    public function exec(kxEnv $environment)
    {
        $twigData['entries'] = self::fillArrayWithFileNodes(new DirectoryIterator(KX_ROOT.'/application/templates/'));
        kxTemplate::output('manage/templates', $twigData);
    }

    // Thanks Peter Bailey - http://stackoverflow.com/questions/952263/deep-recursive-array-of-directory-structure-in-php/952324#952324
    public function fillArrayWithFileNodes(DirectoryIterator $dir)
    {
        $data = [];
        foreach ($dir as $node) {
            if ($node->isDir() && !$node->isDot() && false === strpos($node->getPathName(), '.svn') && false === strpos($node->getPathName(), 'compiled')) {
                $data[$node->getFilename()] = self::fillArrayWithFileNodes(new DirectoryIterator($node->getPathname()));
            } elseif ($node->isFile()) {
                $data[] = $node->getFilename();
            }
        }

        return $data;
    }
}
