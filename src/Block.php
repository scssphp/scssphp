<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp;

use ScssPhp\ScssPhp\Compiler\Environment;

/**
 * Block
 *
 * @author Anthon Pang <anthon.pang@gmail.com>
 *
 * @internal
 */
class Block
{
    /**
     * @var string|null
     */
    public $type;

    /**
     * @var Block|null
     */
    public $parent;

    /**
     * @var string
     */
    public $sourceName;

    /**
     * @var int
     */
    public $sourceIndex;

    /**
     * @var int
     */
    public $sourceLine;

    /**
     * @var int
     */
    public $sourceColumn;

    /**
     * @var array|null
     */
    public $selectors;

    /**
     * @var array
     */
    public $comments;

    /**
     * @var array
     */
    public $children;

    /**
     * @var Block|null
     */
    public $selfParent;

    /**
     * @var bool|null
     */
    public $hasValue;

    /**
     * @var string|array|null
     */
    public $name;

    /**
     * @var array|null
     */
    public $args;

    /**
     * @var Environment|null
     */
    public $parentEnv;

    /**
     * @var Environment|null
     */
    public $scope;

    /**
     * @var array|null
     */
    public $prefix;

    /**
     * The selector of an at-root rule
     *
     * @var array|null
     */
    public $selector;

    /**
     * @var array|null
     */
    public $with;

    /**
     * @var string|array|null
     */
    public $value;

    /**
     * @var Block[]|null
     */
    public $cases;

    /**
     * @var array|null
     */
    public $cond;

    /**
     * @var bool|null
     */
    public $dontAppend;

    /**
     * @var array|null
     */
    public $child;

    /**
     * @var string[]|null
     */
    public $vars;

    /**
     * @var array|null
     */
    public $list;

    /**
     * @var string|null
     */
    public $var;

    /**
     * @var array|null
     */
    public $start;

    /**
     * @var array|null
     */
    public $end;

    /**
     * @var bool|null
     */
    public $until;

    /**
     * @var array|null
     */
    public $queryList;
}
