<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Represents an autoescape node.
 *
 * The value is the escaping strategy (can be html, js, ...)
 *
 * The true value is equivalent to html.
 *
 * If autoescaping is disabled, then the value is false.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Twig_Node_AutoEscape extends Node
{
    public function __construct($value, Node $body, $lineno, $tag = 'autoescape')
    {
        parent::__construct(['body' => $body], ['value' => $value], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->subcompile($this->getNode('body'));
    }
}

class_alias('Twig_Node_AutoEscape', 'Twig\Node\AutoEscapeNode', false);
