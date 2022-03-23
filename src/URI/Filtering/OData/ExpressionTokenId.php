<?php
//phpcs:ignoreFile
declare(strict_types=1);

namespace JSONAPI\URI\Filtering\OData;

/**
 * Class ExpressionTokenId
 *
 * @package JSONAPI\URI\Filtering
 */
enum ExpressionTokenId
{
    //Unknown.
    case UNKNOWN;

    //End of text.
    case END;

    //'=' - equality character.
    case EQUAL;

    //Identifier.
    case IDENTIFIER;

    //NullLiteral.
    case NULL_LITERAL;

    //BooleanLiteral.
    case BOOLEAN_LITERAL;

    //StringLiteral.
    case STRING_LITERAL;

    //IntegerLiteral. (int32)
    case INTEGER_LITERAL;

    //Int64 literal.
    case INT64_LITERAL;

    //Single literal. (float)
    case SINGLE_LITERAL;

    //DateTime literal.
    case DATETIME_LITERAL;

    //Decimal literal.
    case DECIMAL_LITERAL;

    //Double literal.
    case DOUBLE_LITERAL;

    //GUID literal.
    case GUID_LITERAL;

    //Binary literal.
    case BINARY_LITERAL;

    //Exclamation.
    case EXCLAMATION;

    //OpenParen.
    case OPEN_PARAM;

    //CloseParen.
    case CLOSE_PARAM;

    //Comma.
    case COMMA;

    //Minus.
    case MINUS;

    //Slash.
    case SLASH;

    //Question.
    case QUESTION;

    //Dot.
    case DOT;

    //Star.
    case STAR;
}
