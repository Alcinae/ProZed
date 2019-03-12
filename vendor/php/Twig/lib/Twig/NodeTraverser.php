<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Twig_NodeTraverser is a node traverser.
 *
 * It visits all nodes and their children and calls the given visitor for each.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Twig_NodeTraverser
{
    private $env;
    private $visitors = [];

    /**
     * @param NodeVisitorInterface[] $visitors
     */
    public function __construct(Environment $env, array $visitors = [])
    {
        $this->env = $env;
        foreach ($visitors as $visitor) {
            $this->addVisitor($visitor);
        }
    }

    public function addVisitor(NodeVisitorInterface $visitor)
    {
        if (!isset($this->visitors[$visitor->getPriority()])) {
            $this->visitors[$visitor->getPriority()] = [];
        }

        $this->visitors[$visitor->getPriority()][] = $visitor;
    }

    /**
     * Traverses a node and calls the registered visitors.
     *
     * @return Node
     */
    public function traverse(Node $node)
    {
        ksort($this->visitors);
        foreach ($this->visitors as $visitors) {
            foreach ($visitors as $visitor) {
                $node = $this->traverseForVisitor($visitor, $node);
            }
        }

        return $node;
    }

    private function traverseForVisitor(NodeVisitorInterface $visitor, Node $node)
    {
        $node = $visitor->enterNode($node, $this->env);

        foreach ($node as $k => $n) {
            if (false !== $m = $this->traverseForVisitor($visitor, $n)) {
                if ($m !== $n) {
                    $node->setNode($k, $m);
                }
            } else {
                $node->removeNode($k);
            }
        }

        return $visitor->leaveNode($node, $this->env);
    }
}

class_alias('Twig_NodeTraverser', 'Twig\NodeTraverser', false);