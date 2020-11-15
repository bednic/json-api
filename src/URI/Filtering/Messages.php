<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

/**
 * Class Messages
 * Helps to format error messages
 *
 * @package JSONAPI\URI\Filtering
 */
class Messages
{
    /**
     * Format message for unterminated string literal error
     *
     * @param int    $pos  Position of unterminated string literal in the text
     * @param string $text The text with unterminated string literal
     *
     * @return string The formatted message
     */
    public static function expressionLexerUnterminatedStringLiteral(int $pos, string $text)
    {
        return 'Unterminated string literal at position ' . $pos . ' in ' . $text;
    }

    /**
     * Format message for digit expected error
     *
     * @param int $pos Position at which digit is expected
     *
     * @return string The formatted message
     */
    public static function expressionLexerDigitExpected(int $pos)
    {
        return 'Digit expected at position ' . $pos;
    }

    /**
     * Format message for syntax error
     *
     * @param int $pos Position at which syntax error found
     *
     * @return string The formatted message
     */
    public static function expressionLexerSyntaxError(int $pos)
    {
        return 'Syntax Error at position ' . $pos;
    }

    /**
     * Format message for invalid character error
     *
     * @param string $ch  The invalid character found
     * @param int    $pos Position at which invalid character found
     *
     * @return string The formatted message
     */
    public static function expressionLexerInvalidCharacter(string $ch, int $pos)
    {
        return "Invalid character '$ch' at position $pos";
    }

    /**
     * Format message for an unsupported null operation
     *
     * @param string $operator The operator
     * @param int    $pos      Position at which operator with null operands found
     *
     * @return string The formatted message
     */
    public static function expressionParserOperatorNotSupportNull(string $operator, int $pos)
    {
        return "The operator '$operator' at position $pos is not supported for the 'null' literal; only equality
        checks are supported";
    }

    /**
     * Format message for an unrecognized literal
     *
     * @param string $type    The expected literal type
     * @param string $literal The malformed literal
     * @param int    $pos     Position at which literal found
     *
     * @return string The formatted message
     */
    public static function expressionParserUnrecognizedLiteral(string $type, string $literal, int $pos)
    {
        return "Unrecognized '$type' literal '$literal' in position '$pos'.";
    }

    /**
     * Format message for an unknown function-call
     *
     * @param string $str The unknown function name
     * @param int    $pos Position at which unknown function-call found
     *
     * @return string The formatted message
     */
    public static function expressionParserUnknownFunction(string $str, int $pos)
    {
        return "Unknown function '$str' at position $pos";
    }

    /**
     * Message to show error when there is a syntax error in the query
     *
     * @return string The message
     */
    public static function syntaxError()
    {
        return 'Bad Request - Error in query syntax';
    }

    /**
     * Format a message to show error when data service failed to
     * access some of the properties of dummy object
     *
     * @param string $propertyName     Property name
     * @param string $parentObjectName Parent object name
     *
     * @return string The formatted message
     */
    public static function failedToAccessProperty(string $propertyName, string $parentObjectName)
    {
        return "Data Service failed to access or initialize the property [$propertyName] of [$parentObjectName]";
    }

    /**
     * Message thrown when operand or function is not implemented
     *
     * @param string $fnOrOp operand or function string
     *
     * @return string
     */
    public static function operandOrFunctionNotImplemented(string $fnOrOp)
    {
        return "Operand or function [{$fnOrOp}] not implemented.";
    }
}
