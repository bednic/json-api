<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering\OData;

use Exception;

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
        return
            $this->id === ExpressionTokenId::IDENTIFIER &&
            (
                strcmp($this->text, Constants::LOGICAL_EQUAL) === 0 ||
                strcmp($this->text, Constants::LOGICAL_NOT_EQUAL) === 0 ||
                strcmp($this->text, Constants::LOGICAL_LOWER_THAN) === 0 ||
                strcmp($this->text, Constants::LOGICAL_LOWER_THAN_OR_EQUAL) === 0 ||
                strcmp($this->text, Constants::LOGICAL_GREATER_THAN) === 0 ||
                strcmp($this->text, Constants::LOGICAL_GREATER_THAN_OR_EQUAL) === 0 ||
                strcmp($this->text, Constants::LOGICAL_HAS) === 0 ||
                strcmp($this->text, Constants::LOGICAL_IN) === 0
            );
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
            default => false
        } || ExpressionLexer::isNumeric($this->id);
    }

    /**
     * Gets the current identifier text
     *
     * @return string
     * @throws Exception
     */
    public function getIdentifier()
    {
        if ($this->id !== ExpressionTokenId::IDENTIFIER) {
            throw new ExpressionException(
                'Identifier expected at position ' . $this->position
            );
        }

        return $this->text;
    }

    /**
     * Checks that this token has the specified identifier.
     *
     * @param string $keyWord Identifier to check
     *
     * @return bool true if this is an identifier with the specified text
     */
    public function identifierIs(string $keyWord)
    {
        return $this->id === ExpressionTokenId::IDENTIFIER
            && strcmp($this->text, $keyWord) == 0;
    }
}
