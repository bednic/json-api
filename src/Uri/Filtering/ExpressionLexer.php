<?php

namespace JSONAPI\Uri\Filtering;

use Exception;

/**
 * Class ExpressionLexer
 *
 * Lexical analyzer for Astoria URI expression parsing
 * Literals        Representation
 * --------------------------------------------------------------------
 * Null            null
 * Boolean         true | false
 * Int32           (digit+)
 * Int64           (digit+)(L|l)
 * Decimal         (digit+ ['.' digit+])(M|m)
 * Float (Single)  (digit+ ['.' digit+][e|E [+|-] digit+)(f|F)
 * Double          (digit+ ['.' digit+][e|E [+|-] digit+)
 * String          "'" .* "'"
 * DateTime        datetime"'"dddd-dd-dd[T|' ']dd:mm[ss[.fffffff]]"'"
 * Binary          (binary|X)'digit*'
 * GUID            guid'digit*
 *
 * @package POData\UriProcessor\QueryProcessor\ExpressionParser
 * @codeCoverageIgnore
 */
class ExpressionLexer
{
    public const A = 65;
    public const Z = 90;
    public const SMALL_A = 97;
    public const SMALL_Z = 122;
    public const F = 70;
    public const SMALL_F = 102;
    public const ZERO = 48;
    public const NINE = 57;
    public const TAB = 9;
    public const NEWLINE = 10;
    public const CARRIAGE_RETURN = 13;
    public const SPACE = 32;


    /**
     * Suffix for single literals
     *
     * @var string
     */
    public const SINGLE_SUFFIX_LOWER = 'f';

    /**
     * Suffix for single literals
     *
     * @var string
     */
    public const SINGLE_SUFFIX_UPPER = 'F';

    /**
     * Text being parsed
     *
     * @var string[]
     */
    private $text;

    /**
     * Length of text being parsed
     *
     * @var int
     */
    private $textLen;

    /**
     * Position on text being parsed
     *
     * @var int
     */
    private $pos;

    /**
     * Character being processed
     *
     * @var string
     */
    private $ch;

    /**
     * ExpressionToken being processed
     *
     * @var ExpressionToken
     */
    private $token;

    /**
     * ExpressionLexer constructor.
     *
     * @param $expression
     *
     * @throws ExpressionException
     */
    public function __construct($expression)
    {
        $this->text = $expression;
        $this->textLen = strlen($this->text);
        $this->token = new ExpressionToken();
        $this->setTextPos(0);
        $this->nextToken();
    }

    /**
     * Set the text position.
     *
     * @param int $pos Value to position.
     *
     * @return void
     */
    private function setTextPos($pos)
    {
        $this->pos = $pos;
        $this->ch
            = $this->pos < $this->textLen
            ? $this->text[$this->pos] : '\0';
    }

    /**
     * @throws ExpressionException
     */
    public function nextToken()
    {

        while (self::isWhiteSpace($this->ch)) {
            $this->nextChar();
        }

        $t = null;
        $tokenPos = $this->pos;
        switch ($this->ch) {
            case '(':
                $this->nextChar();
                $t = ExpressionTokenId::OPENPARAM();
                break;
            case ')':
                $this->nextChar();
                $t = ExpressionTokenId::CLOSEPARAM();
                break;
            case ',':
                $this->nextChar();
                $t = ExpressionTokenId::COMMA();
                break;
            case '-':
                $hasNext = $this->pos + 1 < $this->textLen;
                if ($hasNext && self::isDigit($this->text[$this->pos + 1])) {
                    $this->nextChar();
                    $t = $this->parseFromDigit();
                    if (self::isNumeric($t)) {
                        break;
                    }

                    $this->setTextPos($tokenPos);
                } elseif ($hasNext && $this->text[$tokenPos + 1] == 'I') {
                    $this->nextChar();
                    $this->parseIdentifier();
                    $currentIdentifier = substr($this->text, $tokenPos + 1, $this->pos - $tokenPos - 1);

                    if (self::isInfinityLiteralDouble($currentIdentifier)) {
                        $t = ExpressionTokenId::DOUBLE_LITERAL();
                        break;
                    } elseif (self::isInfinityLiteralSingle($currentIdentifier)) {
                        $t = ExpressionTokenId::SINGLE_LITERAL();
                        break;
                    }

                    // If it looked like '-INF' but wasn't we'll rewind and fall
                    // through to a simple '-' token.
                    $this->setTextPos($tokenPos);
                }

                $this->nextChar();
                $t = ExpressionTokenId::MINUS();
                break;
            case '=':
                $this->nextChar();
                $t = ExpressionTokenId::EQUAL();
                break;
            case '/':
                $this->nextChar();
                $t = ExpressionTokenId::SLASH();
                break;
            case '?':
                $this->nextChar();
                $t = ExpressionTokenId::QUESTION();
                break;
            case '.':
                $this->nextChar();
                $t = ExpressionTokenId::DOT();
                break;
            case '\'':
                $quote = $this->ch;
                do {
                    $this->nextChar();
                    while ($this->pos < $this->textLen && $this->ch != $quote) {
                        $this->nextChar();
                    }

                    if ($this->pos == $this->textLen) {
                        $this->parseError(
                            Messages::expressionLexerUnterminatedStringLiteral(
                                $this->pos,
                                $this->text
                            )
                        );
                    }

                    $this->nextChar();
                } while ($this->ch == $quote);
                $t = ExpressionTokenId::STRING_LITERAL();
                break;
            case '*':
                $this->nextChar();
                $t = ExpressionTokenId::STAR();
                break;
            default:
                if (self::isLetter($this->ch) || $this->ch == '_') {
                    $this->parseIdentifier();
                    $t = ExpressionTokenId::IDENTIFIER();
                    break;
                }

                if (self::isDigit($this->ch)) {
                    $t = $this->parseFromDigit();
                    break;
                }

                if ($this->pos == $this->textLen) {
                    $t = ExpressionTokenId::END();
                    break;
                }

                $this->parseError(
                    Messages::expressionLexerInvalidCharacter(
                        $this->ch,
                        $this->pos
                    )
                );
        }

        $this->token->id = $t;
        $this->token->text = substr($this->text, $tokenPos, $this->pos - $tokenPos);
        $this->token->position = $tokenPos;

        // Handle type-prefixed literals such as binary, datetime or guid.
        $this->handleTypePrefixedLiterals();

        // Handle keywords.
        if ($this->token->id->equals(ExpressionTokenId::IDENTIFIER())) {
            if (self::isInfinityOrNaNDouble($this->token->text)) {
                $this->token->id->equals(ExpressionTokenId::DOUBLE_LITERAL());
            } elseif (self::isInfinityOrNanSingle($this->token->text)) {
                $this->token->id = ExpressionTokenId::SINGLE_LITERAL();
            } elseif (
                $this->token->text == Constants::KEYWORD_TRUE
                || $this->token->text == Constants::KEYWORD_FALSE
            ) {
                $this->token->id = ExpressionTokenId::BOOLEAN_LITERAL();
            } elseif ($this->token->text == Constants::KEYWORD_NULL) {
                $this->token->id = ExpressionTokenId::NULL_LITERAL();
            }
        }
    }

    /**
     * Checks a character is whilespace
     *
     * @param string $char character to check
     *
     * @return boolean
     */
    public static function isWhiteSpace($char)
    {
        $asciiVal = ord($char);
        return $asciiVal == self::SPACE
            || $asciiVal == self::TAB
            || $asciiVal == self::CARRIAGE_RETURN
            || $asciiVal == self::NEWLINE;
    }

    /**
     * Advance to next character.
     *
     * @return void
     */
    private function nextChar()
    {
        if ($this->pos < $this->textLen) {
            $this->pos++;
        }

        $this->ch
            = $this->pos < $this->textLen
            ? $this->text[$this->pos] : '\0';
    }

    /**
     * Checks a character is digit
     *
     * @param string $char character to check
     *
     * @return boolean
     */
    public static function isDigit($char)
    {
        $asciiVal = ord($char);
        return $asciiVal >= self::ZERO
            && $asciiVal <= self::NINE;
    }

    /**
     * Parses a token that starts with a digit
     *
     * @return ExpressionTokenId The kind of token recognized.
     * @throws ExpressionException
     */
    private function parseFromDigit(): ExpressionTokenId
    {
        $result = null;
        $startChar = $this->ch;
        $this->nextChar();
        if ($startChar == '0' && $this->ch == 'x' || $this->ch == 'X') {
            $result = ExpressionTokenId::BINARY_LITERAL();
            do {
                $this->nextChar();
            } while (ctype_xdigit($this->ch));
        } else {
            $result = ExpressionTokenId::INTEGER_LITERAL();
            while (self::isDigit($this->ch)) {
                $this->nextChar();
            }

            if ($this->ch == '.') {
                $result = ExpressionTokenId::DOUBLE_LITERAL();
                $this->nextChar();
                $this->validateDigit();

                do {
                    $this->nextChar();
                } while (self::isDigit($this->ch));
            }

            if ($this->ch == 'E' || $this->ch == 'e') {
                $result = ExpressionTokenId::DOUBLE_LITERAL();
                $this->nextChar();
                if ($this->ch == '+' || $this->ch == '-') {
                    $this->nextChar();
                }

                $this->validateDigit();
                do {
                    $this->nextChar();
                } while (self::isDigit($this->ch));
            }

            if ($this->ch == 'M' || $this->ch == 'm') {
                $result = ExpressionTokenId::DECIMAL_LITERAL();
                $this->nextChar();
            } elseif ($this->ch == 'd' || $this->ch == 'D') {
                $result = ExpressionTokenId::DOUBLE_LITERAL();
                $this->nextChar();
            } elseif ($this->ch == 'L' || $this->ch == 'l') {
                $result = ExpressionTokenId::INT64_LITERAL();
                $this->nextChar();
            } elseif ($this->ch == 'f' || $this->ch == 'F') {
                $result = ExpressionTokenId::SINGLE_LITERAL();
                $this->nextChar();
            }
        }

        return $result;
    }

    /**
     * Validate current character is a digit.
     *
     * @return void
     * @throws ExpressionException
     */
    private function validateDigit()
    {
        if (!self::isDigit($this->ch)) {
            $this->parseError(
                Messages::expressionLexerDigitExpected(
                    $this->pos
                )
            );
        }
    }

    /**
     * @param $message
     *
     * @throws ExpressionException
     */
    private function parseError($message)
    {
        throw new ExpressionException($message);
    }

    /**
     * Whether the specified token identifier is a numeric literal
     *
     * @param ExpressionTokenId $id Token identifier to check
     *
     * @return bool true if it's a numeric literal; false otherwise
     */
    public static function isNumeric($id)
    {
        return
            $id->equals(ExpressionTokenId::INTEGER_LITERAL())
            || $id->equals(ExpressionTokenId::DECIMAL_LITERAL())
            || $id->equals(ExpressionTokenId::DOUBLE_LITERAL())
            || $id->equals(ExpressionTokenId::INT64_LITERAL())
            || $id->equals(ExpressionTokenId::SINGLE_LITERAL());
    }

    /**
     * Parses an identifier by advancing the current character.
     *
     * @return void
     */
    private function parseIdentifier()
    {
        do {
            $this->nextChar();
        } while (self::isLetterOrDigit($this->ch) || $this->ch == '_');
    }

    /**
     * Checks a character is letter or digit
     *
     * @param string $char character to check
     *
     * @return boolean
     */
    public static function isLetterOrDigit($char)
    {
        return self::isDigit($char) || self::isLetter($char);
    }

    /**
     * Checks a character is letter
     *
     * @param string $char character to check
     *
     * @return boolean
     */
    public static function isLetter($char)
    {
        $asciiVal = ord($char);
        return ($asciiVal >= self::A && $asciiVal <= self::Z)
            || ($asciiVal >= self::SMALL_A && $asciiVal <= self::SMALL_Z);
    }

    /**
     * Check if the parameter ($text) is INF
     *
     * @param string $text Text to look in
     *
     * @return boolean true if match found, false otherwise
     */
    private static function isInfinityLiteralDouble($text)
    {
        return strcmp($text, Constants::XML_INFINITY_LITERAL) == 0;
    }

    /**
     * Checks whether parameter ($text) EQUALS to 'INFf' or 'INFF' at position
     *
     * @param string $text Text to look in
     *
     * @return bool true if the substring is equal using an ordinal comparison;
     *         false otherwise
     */
    private static function isInfinityLiteralSingle($text)
    {
        return strlen($text) == 4
            && ($text[3] == ExpressionLexer::SINGLE_SUFFIX_LOWER
                || $text[3] == ExpressionLexer::SINGLE_SUFFIX_UPPER)
            && strncmp($text, Constants::XML_INFINITY_LITERAL, 3) == 0;
    }

    /**
     * Handles the literals that are prefixed by types.
     * This method modified the token field as necessary.
     *
     * @return void
     *
     * @throws Exception
     */
    private function handleTypePrefixedLiterals()
    {
        $id = $this->token->id;
        if (!$id->equals(ExpressionTokenId::IDENTIFIER())) {
            return;
        }

        $quoteFollows = $this->ch == '\'';
        if (!$quoteFollows) {
            return;
        }

        $tokenText = $this->token->text;

        if (strcasecmp('datetime', $tokenText) == 0) {
            $id = ExpressionTokenId::DATETIME_LITERAL();
        } elseif (strcasecmp('guid', $tokenText) == 0) {
            $id = ExpressionTokenId::GUID_LITERAL();
        } elseif (
            strcasecmp('binary', $tokenText) == 0
            || strcasecmp('X', $tokenText) == 0
            || strcasecmp('x', $tokenText) == 0
        ) {
            $id = ExpressionTokenId::BINARY_LITERAL();
        } else {
            return;
        }

        $tokenPos = $this->token->position;
        do {
            $this->nextChar();
        } while ($this->ch != '\0' && $this->ch != '\'');

        if ($this->ch == '\0') {
            $this->parseError(
                Messages::expressionLexerUnterminatedStringLiteral(
                    $this->pos,
                    $this->text
                )
            );
        }

        $this->nextChar();
        $this->token->id = $id;
        $this->token->text = substr($this->text, $tokenPos, $this->pos - $tokenPos);
    }

    /**
     * Check if the parameter ($tokenText) is INF or NaN
     *
     * @param string $tokenText Text to look in
     *
     * @return boolean true if match found, false otherwise
     */
    private static function isInfinityOrNaNDouble($tokenText)
    {
        if (strlen($tokenText) == 3) {
            if ($tokenText[0] == 'I') {
                return self::isInfinityLiteralDouble($tokenText);
            } elseif ($tokenText[0] == 'N') {
                return strncmp($tokenText, Constants::XML_NAN_LITERAL, 3) == 0;
            }
        }

        return false;
    }

    /**
     * Checks if the parameter ($tokenText) is INFf/INFF or NaNf/NaNF.
     *
     * @param string $tokenText Input token
     *
     * @return bool true if match found, false otherwise
     */
    private static function isInfinityOrNanSingle($tokenText)
    {
        if (strlen($tokenText) == 4) {
            if ($tokenText[0] == 'I') {
                return self::isInfinityLiteralSingle($tokenText);
            } elseif ($tokenText[0] == 'N') {
                return ($tokenText[3] == ExpressionLexer::SINGLE_SUFFIX_LOWER
                        || $tokenText[3] == ExpressionLexer::SINGLE_SUFFIX_UPPER)
                    && strncmp($tokenText, Constants::XML_NAN_LITERAL, 3) == 0;
            }
        }

        return false;
    }

    /**
     * To get the expression token being processed
     *
     * @return ExpressionToken
     */
    public function getCurrentToken()
    {
        return $this->token;
    }

    /**
     * To set the token being processed
     *
     * @param ExpressionToken $token The expression token to set as current
     *
     * @return void
     */
    public function setCurrentToken($token)
    {
        $this->token = $token;
    }

    /**
     * To get the text being parsed
     *
     * @return string
     */
    public function getExpressionText()
    {
        return $this->text;
    }

    /**
     * Position of the current token in the text being parsed
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->token->position;
    }

    /**
     * Returns the next token without advancing the lexer to next token
     *
     * @return ExpressionToken
     * @throws ExpressionException
     */
    public function peekNextToken(): ExpressionToken
    {
        $savedTextPos = $this->pos;
        $savedChar = $this->ch;
        $savedToken = clone $this->token;
        $this->nextToken();
        $result = clone $this->token;
        $this->pos = $savedTextPos;
        $this->ch = $savedChar;
        $this->token->id = $savedToken->id;
        $this->token->position = $savedToken->position;
        $this->token->text = $savedToken->text;
        return $result;
    }

    /**
     * Starting from an identifier, reads alternate sequence of dots and identifiers
     * and returns the text for it
     *
     * @return string The dotted identifier starting at the current identifier
     * @throws Exception
     */
    public function readDottedIdentifier()
    {
        $this->validateToken(ExpressionTokenId::IDENTIFIER());
        $identifier = $this->token->text;
        $this->nextToken();
        while ($this->token->id->equals(ExpressionTokenId::DOT())) {
            $this->nextToken();
            $this->validateToken(ExpressionTokenId::IDENTIFIER());
            $identifier = $identifier . '.' . $this->token->text;
            $this->nextToken();
        }

        return $identifier;
    }

    /**
     * Validates the current token is of the specified kind
     *
     * @param ExpressionTokenId $tokenId Expected token kind
     *
     * @return void
     *
     * @throws Exception if current token is not of the
     *                        specified kind.
     */
    public function validateToken(ExpressionTokenId $tokenId)
    {
        if ($this->token->id->equals($tokenId)) {
            $this->parseError(
                Messages::expressionLexerSyntaxError(
                    $this->pos
                )
            );
        }
    }
}
