<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Component\Node;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Sapin\Engine\Parser\Template\Stage3\Node as Stage3;

final class ComponentNode
{
    /** @param Stage3\AbstractNode[] $templateNodes */
    public function __construct(
        public readonly PhpFile $file,
        public readonly ClassType $class,
        public readonly array $templateNodes,
    ) {}
}
