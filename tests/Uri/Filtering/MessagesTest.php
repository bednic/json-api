<?php

declare(strict_types=1);

namespace JSONAPI\Test\Uri\Filtering;

use JSONAPI\Uri\Filtering\Messages;
use PHPUnit\Framework\TestCase;

class MessagesTest extends TestCase
{

    public function testFailedToAccessProperty()
    {
        $this->assertIsString(Messages::failedToAccessProperty('name', 'object'));
    }

    public function testExpressionLexerDigitExpected()
    {
        $this->assertIsString(Messages::expressionLexerDigitExpected(1));
    }

    public function testExpressionParserOperatorNotSupportNull()
    {
        $this->assertIsString(Messages::expressionParserOperatorNotSupportNull('operator', 1));
    }

    public function testExpressionLexerSyntaxError()
    {
        $this->assertIsString(Messages::expressionLexerSyntaxError(1));
    }

    public function testOperandOrFunctionNotImplemented()
    {
        $this->assertIsString(Messages::OperandOrFunctionNotImplemented('fnOrOp'));
    }

    public function testExpressionLexerInvalidCharacter()
    {
        $this->assertIsString(Messages::expressionLexerInvalidCharacter('ch', 1));
    }

    public function testExpressionParserUnrecognizedLiteral()
    {
        $this->assertIsString(Messages::ExpressionParserUnrecognizedLiteral('type', 'literal', 1));
    }

    public function testSyntaxError()
    {
        $this->assertIsString(Messages::SyntaxError());
    }

    public function testExpressionLexerUnterminatedStringLiteral()
    {
        $this->assertIsString(Messages::expressionLexerUnterminatedStringLiteral(1, 'text'));
    }

    public function testExpressionParserUnknownFunction()
    {
        $this->assertIsString(Messages::expressionParserUnknownFunction('str', 1));
    }
}
