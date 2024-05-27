<?php

namespace Sapin\Engine\Ast\Node\Template;

use Sapin\Engine\Ast\Compiler;
use Sapin\Engine\Ast\Node\AbstractNode;
use Sapin\Engine\SapinException;

final class TemplateNode extends AbstractNode
{
    /** @var array<string, string> */
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
     * @throws SapinException
     */
    public function addUse(string $componentName, string $componentFqn): void
    {
        array_key_exists($componentName, $this->usesMap) && throw new SapinException(sprintf(
            'Another component named "%s" was previously imported. Consider using the aliasing syntax',
            $componentName
        ));

        $this->usesMap[$componentName] = $componentFqn;
    }

    public function getUse(string $componentName): ?string
    {
        return $this->usesMap[$componentName] ?? null;
    }
}
