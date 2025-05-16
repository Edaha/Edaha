<?php
use PHPUnit\Framework\TestCase;

final class ModuleTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $module = new Edaha\Entities\Module(
            'name',
            Edaha\Types\ModuleType::BoardRegenerator,
            'Edaha\Renderers\TextBoardRegenerator',
            'description',
            false
        );

        $this->assertSame('name', $module->name);
        $this->assertSame(Edaha\Types\ModuleType::BoardRegenerator, $module->type);
        $this->assertSame('Edaha\Renderers\TextBoardRegenerator', $module->class);
        $this->assertSame('description', $module->description);
        $this->assertFalse($module->is_manage);
    }

    public function testCanNotBeCreatedWithUnloadableClass(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Edaha\Entities\Module(
            'name',
            Edaha\Types\ModuleType::BoardRegenerator,
            'InvalidClassName',
            'description',
            false
        );
    }
}