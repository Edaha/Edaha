<?php
/**
 * Edaha - Module Management
 */

use Edaha\Entities\Module;

class manage_core_modules_modules extends kxCmd
{
    /**
     * Arguments eventually being sent to twig
     *
     * @var Array()
     */
    protected $twigData;

    public function exec(kxEnv $environment)
    {
        $this->twigData['modules'] = $this->entityManager->getRepository(Module::class)->findAll();

        switch ($this->request['action']) {
            case 'add':
                $this->_add();
                break;
            case 'del':
                $this->_del();
                break;
        }
        
        kxTemplate::output("manage/modules", $this->twigData);
    }

    protected function _add()
    {
        // Logic for adding a module
        // check if module already exists
        $existingModule = $this->entityManager->getRepository(Module::class)->findOneBy(['name' => $this->request['name']]);
        if ($existingModule) {
            throw new Exception('Module with this name already exists');
        }

        $module = new Module(
            name: $this->request['name'],
            type: $this->request['type'],
            class: $this->request['class'],
            description: $this->request['description'],
            is_manage: $this->request['is_manage'] ?? false
        );

        $this->entityManager->persist($module);
        $this->entityManager->flush();
    }

    protected function _del()
    {
        // Logic for deleting a module
        $module = $this->entityManager->getRepository(Module::class)->find($this->request['id']);
        if (!$module) {
            throw new Exception('Module not found');
        }

        $this->entityManager->remove($module);
        $this->entityManager->flush();
    }
}