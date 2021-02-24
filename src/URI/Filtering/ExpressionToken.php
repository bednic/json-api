<?php

declare(strict_types=1);

namespace JSONAPI\URI\Filtering;

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
    public $id;

    /**
     * @var string
     */
    public $text;

    /**
     * @var int
     */
    public $position;

    /**
     * Checks whether this token is a comparison operator.
     *
     * @return bool True if this token represent a comparison operator
     *                 False otherwise.
     */
    public function isComparisonOperator(): bool
    {
        return
            $this->id->equals(ExpressionTokenId::IDENTIFIER()) &&
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
    public function isKeyValueToken()
    {
        return
            $this->id->equals(ExpressionTokenId::BINARY_LITERAL()) ||
            $this->id->equals(ExpressionTokenId::BOOLEAN_LITERAL()) ||
            $this->id->equals(ExpressionTokenId::DATETIME_LITERAL()) ||
            $this->id->equals(ExpressionTokenId::GUID_LITERAL()) ||
            $this->id->equals(ExpressionTokenId::STRING_LITERAL()) ||
            $this->id->equals(ExpressionTokenId::NULL_LITERAL()) ||
            ExpressionLexer::isNumeric($this->id);
    }

    /**
     * Gets the current identifier text
     *
     * @return string
     * @throws Exception
     */
    public function getIdentifier()
    {
        if (!$this->id->equals(ExpressionTokenId::IDENTIFIER())) {
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
        return $this->id->equals(ExpressionTokenId::IDENTIFIER())
            && strcmp($this->text, $keyWord) == 0;
    }
}
