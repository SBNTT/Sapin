<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

enum SpecialAttribute: string
{
    case FOREACH = 'foreach';
    case FOR = 'for';
    case IF = 'if';
    case ELSE_IF = 'else-if';
    case ELSE = 'else';
    case SLOT = 'slot';

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
