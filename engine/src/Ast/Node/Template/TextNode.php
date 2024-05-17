<?php

namespace Sapin\Ast\Node\Template;

use Sapin\Ast\Compiler;

final class TextNode extends TemplateElementNode
{
    public function __construct(
        private readonly string $content,
    ) {
        parent::__construct();
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->write(preg_replace_callback(
            '/{{(.*)}}/mU',
            function ($matches) {
                return '<?php \Sapin\Sapin::echo(' . trim($matches[1]) . ');?>';
            },
            trim($this->content)
        ) ?? '');
    }

    public function isEmpty(): bool
    {
        return $this->content === '';
    }
}
