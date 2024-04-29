<?php

namespace Sapin\Ast\Node\Template;

use Exception;
use Sapin\Ast\Compiler;
use Sapin\Ast\Node\AbstractNode;

final class TemplateNode extends AbstractNode
{
    /** @var array<string, class-string> */
    private array $usesMap;

    public function __construct()
    {
        parent::__construct();
        $this->usesMap = [];
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->compileNodes($this->children);
    }

    /**
     * @param class-string $componentFqn
     * @throws Exception
     */
    public function addUse(string $componentName, string $componentFqn): void
    {
        $componentName = strtolower($componentName);
        array_key_exists($componentName, $this->usesMap)
        && throw new Exception('Another component named "' . $componentName . '" already exists.');

        $this->usesMap[$componentName] = $componentFqn;
    }

    public function getUse(string $componentName): ?string
    {
        return $this->usesMap[$componentName] ?? null;
    }
}
