<?php

namespace ScssPhp\ScssPhp\Visitor;

use ScssPhp\ScssPhp\Ast\Selector\AttributeSelector;
use ScssPhp\ScssPhp\Ast\Selector\ClassSelector;
use ScssPhp\ScssPhp\Ast\Selector\ComplexSelector;
use ScssPhp\ScssPhp\Ast\Selector\ComplexSelectorComponent;
use ScssPhp\ScssPhp\Ast\Selector\CompoundSelector;
use ScssPhp\ScssPhp\Ast\Selector\IDSelector;
use ScssPhp\ScssPhp\Ast\Selector\ParentSelector;
use ScssPhp\ScssPhp\Ast\Selector\PlaceholderSelector;
use ScssPhp\ScssPhp\Ast\Selector\PseudoSelector;
use ScssPhp\ScssPhp\Ast\Selector\SelectorList;
use ScssPhp\ScssPhp\Ast\Selector\SimpleSelector;
use ScssPhp\ScssPhp\Ast\Selector\TypeSelector;
use ScssPhp\ScssPhp\Ast\Selector\UniversalSelector;
use ScssPhp\ScssPhp\Util\ListUtil;

/**
 * A {@see SelectorVisitor} whose `visit*` methods default to returning `null`, but
 * which returns the first non-`null` value returned by any method.
 *
 * This can be extended to find the first instance of particular nodes in the
 * AST.
 *
 * @template T
 * @template-implements SelectorVisitor<T|null>
 *
 * @internal
 */
abstract class SelectorSearchVisitor implements SelectorVisitor
{
    public function visitAttributeSelector(AttributeSelector $attribute)
    {
        return null;
    }

    public function visitClassSelector(ClassSelector $klass)
    {
        return null;
    }

    public function visitIDSelector(IDSelector $id)
    {
        return null;
    }

    public function visitParentSelector(ParentSelector $parent)
    {
        return null;
    }

    public function visitPlaceholderSelector(PlaceholderSelector $placeholder)
    {
        return null;
    }

    public function visitTypeSelector(TypeSelector $type)
    {
        return null;
    }

    public function visitUniversalSelector(UniversalSelector $universal)
    {
        return null;
    }

    public function visitComplexSelector(ComplexSelector $complex)
    {
        return ListUtil::search($complex->getComponents(), function (ComplexSelectorComponent $component) {
            return $this->visitCompoundSelector($component->getSelector());
        });
    }

    public function visitCompoundSelector(CompoundSelector $compound)
    {
        return ListUtil::search($compound->getComponents(), function (SimpleSelector $simple) {
            return $simple->accept($this);
        });
    }

    public function visitPseudoSelector(PseudoSelector $pseudo)
    {
        if ($pseudo->getSelector() !== null) {
            return $this->visitSelectorList($pseudo->getSelector());
        }

        return null;
    }

    public function visitSelectorList(SelectorList $list)
    {
        return ListUtil::search($list->getComponents(), function (ComplexSelector $component) {
            return $this->visitComplexSelector($component);
        });
    }
}
