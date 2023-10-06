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

/**
 * A deprecated feature in the language.
 *
 * Code consuming this enum outside Scssphp must not rely on exhaustiveness checks. New values will be added
 * in this enum in minor versions of the package without considering that as a BC break.
 */
enum Deprecation: string
{
    /**
     * Deprecation for passing a string to `call` instead of `get-function`.
     */
    case callString = 'call-string';

    /**
     * Deprecation for `@elseif`.
     */
    case elseif = 'elseif';

    /**
     * Deprecation for parsing `@-moz-document`.
     */
    case mozDocument = 'moz-document';

    /**
     * Deprecation for declaring new variables with `!global`.
     */
    case newGlobal = 'new-global';

    /**
     * Deprecation for treating `/` as division.
     */
    case slashDiv = 'slash-div';

    /**
     * Deprecation for leading, trailing, and repeated combinators.
     */
    case bogusCombinators = 'bogus-combinators';

    /**
     * Deprecation for ambiguous `+` and `-` operators.
     */
    case strictUnary = 'strict-unary';

    /**
     * Deprecation for passing invalid units to certain built-in functions.
     */
    case functionUnits = 'function-units';

    /**
     * Deprecation for passing percentages to the Sass abs() function.
     */
    case absPercent = 'abs-percent';

    case duplicateVariableFlags = 'duplicate-var-flags';

    /**
     * Used for deprecations coming from user-authored code.
     */
    case userAuthored = 'user-authored';

    public function getDescription(): ?string
    {
        return match ($this) {
            self::callString => 'Passing a string directly to meta.call().',
            self::elseif => '@elseif.',
            self::mozDocument => '@-moz-document.',
            self::newGlobal => 'Declaring new variables with !global.',
            self::slashDiv => '/ operator for division.',
            self::bogusCombinators => 'Leading, trailing, and repeated combinators.',
            self::strictUnary => 'Ambiguous + and - operators.',
            self::functionUnits => 'Passing invalid units to built-in functions.',
            self::absPercent => 'Passing percentages to the Sass abs() function.',
            self::duplicateVariableFlags => 'Using !default or !global multiple times for one variable.',
            self::userAuthored => null,
        };
    }

    /**
     * The version in which this feature was first deprecated.
     */
    public function getDeprecatedIn(): ?string
    {
        return match ($this) {
            self::callString => '1.2.0',
            self::elseif => '2.0.0',
            self::mozDocument => '2.0.0',
            self::newGlobal => '2.0.0',
            self::slashDiv => null,
            self::bogusCombinators => '2.0.0',
            self::strictUnary => '2.0.0',
            self::functionUnits => '2.0.0',
            self::absPercent => '2.0.0',
            self::duplicateVariableFlags => '2.0.0',
            self::userAuthored => null,
        };
    }

    public function isFuture(): bool
    {
        if ($this === self::userAuthored) {
            return false;
        }

        return $this->getDeprecatedIn() === null;
    }

    public function getStatus(): DeprecationStatus
    {
        if ($this === self::userAuthored) {
            return DeprecationStatus::user;
        }

        if ($this->isFuture()) {
            return DeprecationStatus::future;
        }

        return DeprecationStatus::active;
    }
}
