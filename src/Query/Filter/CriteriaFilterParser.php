<?php

namespace JSONAPI\Query\Filter;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use Exception;
use JSONAPI\Exception\InvalidArgumentException;
use JSONAPI\Query\Filter;

/**
 * Class Parser
 *
 * @package JSONAPI\Query\Filter
 */
class CriteriaFilterParser implements Filter
{

    private $functions = [
        Constants::FN_CONTAINS,
        Constants::FN_ENDS_WITH,
        Constants::FN_STARTS_WITH,
        Constants::FN_IN,
        Constants::FN_NOT_IN
    ];

    /**
     * @var ExpressionLexer
     */
    private $lexer;

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * Parser constructor.
     */
    public function __construct()
    {
        $this->criteria = Criteria::create();
    }

    /**
     * @param string $filter
     *
     * @throws ExpressionException
     */
    public function parse($filter): void
    {
        if (!is_string($filter)) {
            //todo: this should by something like invalid argument exception
            throw new ExpressionException("Filter have to be a string");
        }
        $this->lexer = new ExpressionLexer($filter);
        $exp = $this->parseExpression();
        $this->criteria->where($exp);
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseExpression(): Expression
    {
        return $this->parseLogicalOr();
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseLogicalOr(): Expression
    {
        $left = $this->parseLogicalAnd();
        while ($this->lexer->getCurrentToken()->identifierIs(Constants::KEYWORD_OR)) {
            $this->lexer->nextToken();
            $right = $this->parseLogicalAnd();
            $left = Criteria::expr()->orX($left, $right);
        }
        return $left;
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseLogicalAnd(): Expression
    {
        $left = $this->parseStart();
        while ($this->lexer->getCurrentToken()->identifierIs(Constants::KEYWORD_AND)) {
            $this->lexer->nextToken();
            $right = $this->parseStart();
            $left = Criteria::expr()->andX($left, $right);
        }
        return $left;
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseStart()
    {
        $token = $this->lexer->getCurrentToken();
        switch ($token->id) {
            case ExpressionTokenId::IDENTIFIER():
                return $this->parseIdentifier();
            case ExpressionTokenId::OPENPARAM():
                return $this->parseGroup();
            case $token->isKeyValueToken():
                return $this->parseValue();
            default:
                $this->err(Messages::expressionLexerSyntaxError($this->lexer->getPosition()));
        }
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseIdentifier(): Expression
    {
        if ($this->lexer->peekNextToken()->id->equals(ExpressionTokenId::OPENPARAM())) {
            $fn = $this->lexer->getCurrentToken()->text;
            // function
            if (in_array($fn, $this->functions)) { // function should have variable count of parameters
                $this->lexer->nextToken(); // (
                $this->lexer->nextToken(); // field
                $field = $this->lexer->getCurrentToken()->text;
                $this->lexer->nextToken(); // ,
                $this->lexer->nextToken(); // value
                $params = [];
                while ($this->lexer->getCurrentToken()->isKeyValueToken()) {
                    $value = $this->validateValue($this->lexer->getCurrentToken());
                    $params[] = $value;
                    $this->lexer->nextToken(); // , | )
                    if ($this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::CLOSEPARAM())) {
                        break;
                    }
                    $this->lexer->nextToken(); // value
                }
                if (!$this->lexer->getCurrentToken()->id->equals(ExpressionTokenId::CLOSEPARAM())) {
                    $this->err(Messages::expressionLexerSyntaxError($this->lexer->getPosition()));
                }
                $this->lexer->nextToken(); // and, or, END
                if (
                    $fn === Constants::FN_IN ||
                    $fn === Constants::FN_NOT_IN
                ) {
                    return Criteria::expr()->{$fn}($field, $params);
                }
                return Criteria::expr()->{$fn}($field, ...$params);
            }
            return $this->err(Messages::expressionParserUnknownFunction(
                $this->lexer->getExpressionText(),
                $this->lexer->getPosition()
            ));
        } else {
            $field = $this->lexer->getCurrentToken()->text;
            $this->lexer->nextToken();
            if (!$this->lexer->getCurrentToken()->isComparisonOperator()) {
                $this->err(Messages::expressionErrorComparisonOperatorExpected(
                    $this->lexer->getCurrentToken()->text,
                    $this->lexer->getPosition()
                ));
            }
            $operator = $this->lexer->getCurrentToken()->text;
            $this->lexer->nextToken();
            $value = $this->validateValue($this->lexer->getCurrentToken());
            $this->lexer->nextToken();
            return Criteria::expr()->{$operator}($field, $value);
        }
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseValue(): Expression
    {
        $value = $this->validateValue($this->lexer->getCurrentToken());
        $this->lexer->nextToken();
        if (!$this->lexer->getCurrentToken()->isComparisonOperator()) {
            $this->err(Messages::expressionErrorComparisonOperatorExpected(
                $this->lexer->getCurrentToken()->text,
                $this->lexer->getPosition()
            ));
        }
        $operator = $this->lexer->getCurrentToken()->text;
        $this->lexer->nextToken();
        $field = $this->lexer->getCurrentToken()->text;
        $this->lexer->nextToken();
        return Criteria::expr()->{$operator}($field, $value);
    }

    /**
     * @param ExpressionToken $token
     *
     * @return mixed
     * @throws ExpressionException
     */
    private function validateValue(ExpressionToken $token)
    {
        if (!$token->isKeyValueToken()) {
            $this->err(Messages::expressionLexerSyntaxError($this->lexer->getPosition()));
        }
        $value = $token->text;
        if ($token->id->equals(ExpressionTokenId::NULL_LITERAL())) {
            $value = null;
        } elseif ($token->id->equals(ExpressionTokenId::DATETIME_LITERAL())) {
            try {
                $value = new DateTime(trim($value, 'datetime\''));
            } catch (Exception $e) {
                $this->err(Messages::syntaxError());
            }
        } elseif ($token->id->equals(ExpressionTokenId::STRING_LITERAL())) {
            $value = trim($value, '\'');
        } elseif ($token->id->equals(ExpressionTokenId::INTEGER_LITERAL())) {
            $value = filter_var($value, FILTER_VALIDATE_INT);
        } elseif ($token->id->equals(ExpressionTokenId::DECIMAL_LITERAL())) {
            $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        }
        return $value;
    }

    /**
     * @param $msg
     *
     * @throws ExpressionException
     */
    private function err($msg)
    {
        throw new ExpressionException($msg);
    }

    /**
     * @return Expression
     * @throws ExpressionException
     */
    private function parseGroup()
    {
        $this->lexer->nextToken(); // (
        $exp = $this->parseExpression();
        $this->lexer->nextToken(); // )
        return $exp;
    }

    /**
     * @return Criteria
     */
    public function getCondition(): Criteria
    {
        return $this->criteria;
    }
}
