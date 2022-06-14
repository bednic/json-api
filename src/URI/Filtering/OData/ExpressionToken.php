<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\OData;

use JSONAPI\URI\Filtering\ExpressionException;
use JSONAPI\URI\Filtering\KeyWord;

/**
 * Class ExpressionToken
 *
 * @package JSONAPI\URI\Filtering
 */
class ExpressionToken
{
    /**
     * @var ExpressionTokenId
     */
    public ExpressionTokenId $id;

    /**
     * @var string
     */
    public string $text;

    /**
     * @var int
     */
    public int $position;

    /**
     * Checks whether this token is a comparison operator.
     *
     * @return bool True if this token represent a comparison operator
     *                 False otherwise.
     */
    public function isComparisonOperator(): bool
    {
        $op = KeyWord::tryFrom($this->text);
        return $op !== null && $this->id === ExpressionTokenId::IDENTIFIER && match ($op) {
                KeyWord::LOGICAL_EQUAL, KeyWord::LOGICAL_NOT_EQUAL,
                KeyWord::LOGICAL_LOWER_THAN, KeyWord::LOGICAL_LOWER_THAN_OR_EQUAL,
                KeyWord::LOGICAL_GREATER_THAN, KeyWord::LOGICAL_GREATER_THAN_OR_EQUAL,
                KeyWord::LOGICAL_IN, KeyWord::LOGICAL_BETWEEN, KeyWord::LOGICAL_HAS => true,
                default             => false
        };
    }

    /**
     * Checks whether this token is a valid token for a key value.
     *
     * @return bool True if this token represent valid key value
     *                 False otherwise.
     */
    public function isKeyValueToken(): bool
    {
        return match ($this->id) {
                   ExpressionTokenId::BINARY_LITERAL,
                   ExpressionTokenId::BOOLEAN_LITERAL,
                   ExpressionTokenId::DATETIME_LITERAL,
                   ExpressionTokenId::GUID_LITERAL,
                   ExpressionTokenId::STRING_LITERAL,
                   ExpressionTokenId::NULL_LITERAL => true,
                   default                         => false
        } || ExpressionLexer::isNumeric($this->id);
    }

    /**
     * Gets the current identifier text
     *
     * @return KeyWord
     * @throws ExpressionException
     */
    public function getIdentifier(): KeyWord
    {
        if ($this->id !== ExpressionTokenId::IDENTIFIER) {
            throw new ExpressionException(
                'Identifier expected at position ' . $this->position
            );
        }

        return KeyWord::tryFrom($this->text);
    }

    /**
     * Checks that this token has the specified identifier.
     *
     * @param KeyWord $keyWord Identifier to check
     *
     * @return bool true if this is an identifier with the specified text
     */
    public function identifierIs(KeyWord $keyWord): bool
    {
        $op = KeyWord::tryFrom($this->text);
        return $this->id === ExpressionTokenId::IDENTIFIER && $op === $keyWord;
    }
}
