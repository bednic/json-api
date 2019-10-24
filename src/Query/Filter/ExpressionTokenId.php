<?php

namespace JSONAPI\Query\Filter;

use MyCLabs\Enum\Enum;

/**
 * Class ExpressionTokenId
 *
 * @package JSONAPI\Query\Filter
 * @method static ExpressionTokenId UNKNOWN()
 * @method static ExpressionTokenId END()
 * @method static ExpressionTokenId EQUAL()
 * @method static ExpressionTokenId IDENTIFIER()
 * @method static ExpressionTokenId NULL_LITERAL()
 * @method static ExpressionTokenId BOOLEAN_LITERAL()
 * @method static ExpressionTokenId STRING_LITERAL()
 * @method static ExpressionTokenId INTEGER_LITERAL()
 * @method static ExpressionTokenId INT64_LITERAL()
 * @method static ExpressionTokenId SINGLE_LITERAL()
 * @method static ExpressionTokenId DATETIME_LITERAL()
 * @method static ExpressionTokenId DECIMAL_LITERAL()
 * @method static ExpressionTokenId DOUBLE_LITERAL()
 * @method static ExpressionTokenId GUID_LITERAL()
 * @method static ExpressionTokenId BINARY_LITERAL()
 * @method static ExpressionTokenId EXCLAMATION()
 * @method static ExpressionTokenId OPENPARAM()
 * @method static ExpressionTokenId CLOSEPARAM()
 * @method static ExpressionTokenId COMMA()
 * @method static ExpressionTokenId MINUS()
 * @method static ExpressionTokenId SLASH()
 * @method static ExpressionTokenId QUESTION()
 * @method static ExpressionTokenId DOT()
 * @method static ExpressionTokenId STAR()
 * @codeCoverageIgnore
 */
class ExpressionTokenId extends Enum
{
    //Unknown.
    public const UNKNOWN = 1;

    //End of text.
    public const END = 2;

    //'=' - equality character.
    public const EQUAL = 3;

    //Identifier.
    public const IDENTIFIER = 4;

    //NullLiteral.
    public const NULL_LITERAL = 5;

    //BooleanLiteral.
    public const BOOLEAN_LITERAL = 6;

    //StringLiteral.
    public const STRING_LITERAL = 7;

    //IntegerLiteral. (int32)
    public const INTEGER_LITERAL = 8;

    //Int64 literal.
    public const INT64_LITERAL = 9;

    //Single literal. (float)
    public const SINGLE_LITERAL = 10;

    //DateTime literal.
    public const DATETIME_LITERAL = 11;

    //Decimal literal.
    public const DECIMAL_LITERAL = 12;

    //Double literal.
    public const DOUBLE_LITERAL = 13;

    //GUID literal.
    public const GUID_LITERAL = 14;

    //Binary literal.
    public const BINARY_LITERAL = 15;

    //Exclamation.
    public const EXCLAMATION = 16;

    //OpenParen.
    public const OPENPARAM = 17;

    //CloseParen.
    public const CLOSEPARAM = 18;

    //Comma.
    public const COMMA = 19;

    //Minus.
    public const MINUS = 20;

    //Slash.
    public const SLASH = 21;

    //Question.
    public const QUESTION = 22;

    //Dot.
    public const DOT = 23;

    //Star.
    public const STAR = 24;
}
