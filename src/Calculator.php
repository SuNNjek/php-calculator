<?php

require_once "Lexer.php";
require_once "Stack.php";
require_once "Queue.php";

class Expression
{
	public $tokens;
	public $var;

	public function __construct(array $tokens, string $var)
	{
		$this->tokens = $tokens;
		$this->var = $var;
	}

	public function __toString()
	{
		return join(
			' ',
			array_map(
				function($t) { return $t->Value; },
				$this->tokens
				)
			);
	}
}

class Calculator
{
	public static function CompileExpression(string $expression, string $var)
	{
		return Calculator::ShuntingYard($expression, $var);
	}

	public static function EvaluateExpression(string $expression, string $var, float $value)
	{
		$compiledExpr = Calculator::CompileExpression($expression, $var);
		return Calculator::EvaluateCompiledExpression($compiledExpr, $value);
	}

	public static function EvaluateCompiledExpression(Expression $expression, float $value)
	{
		$stack = new Stack();

		foreach($expression->tokens as $token)
		{
			switch($token->Type)
			{
				case "Number":
					$stack->Push(floatval($token->Value));
					break;

				case "Variable":

					switch ($token->Value)
					{
						case $expression->var:
							$stack->Push(floatval($value));
							break;
						case "Pi":
							$stack->Push(M_PI);
							break;
						case "E":
							$stack->Push(M_E);
							break;
					}
					break;

				case "Plus":
					$second = $stack->Pop();
					$first = $stack->Pop();

					$stack->Push($first + $second);
					break;
				case "Minus":
					$second = $stack->Pop();
					$first = $stack->Pop();

					$stack->Push($first - $second);
					break;
				case "Multiplication":
					$second = $stack->Pop();
					$first = $stack->Pop();

					$stack->Push($first * $second);
					break;
				case "Division":
					$second = $stack->Pop();
					$first = $stack->Pop();

					$stack->Push($first / $second);
					break;

				case "Modulo":
					$second = $stack->Pop();
					$first = $stack->Pop();

					$stack->Push(fmod($first, $second));
					break;

				case "Exponentiation":
					$exponent = $stack->Pop();
					$base = $stack->Pop();

					$stack->Push(pow($base, $exponent));
					break;

				case "Identifier":
					switch($token->Value)
					{
						case "sqrt":
							$stack->Push(sqrt($stack->Pop()));
							break;
						case "root":
							$root = $stack->Pop();
							$num = $stack->Pop();

							$stack->Push(pow($num, 1 / $root));
							break;

						case "abs":
							$stack->Push(abs($stack->Pop()));
							break;

						case "sign":
							$value = $stack->Pop();
							if($value > 0)
								$stack->Push(1);
							else if($value < 0)
								$stack->Push(-1);
							else
								$stack->Push(0);
							break;

						case "sin":
							$stack->Push(sin($stack->Pop()));
							break;
						case "cos":
							$stack->Push(cos($stack->Pop()));
							break;
						case "tan":
							$stack->Push(tan($stack->Pop()));
							break;

						case "ln":
							$stack->Push(log($stack->Pop()));
							break;
						case "log":
							$base = $stack->Pop();
							$num = $stack->Pop();

							$stack->Push(log($num, $base));
							break;

						default:
							$stack->Push($token->Value);
							break;
					}

					break;
            }
        }

		if($stack->IsEmpty())
			throw new Exception("Mismatched operators");

		$res = $stack->Pop();

		if(!$stack->IsEmpty())
			throw new Exception("Mismatched operands");

        return $res;
    }
	
	private static function ShuntingYard(string $expression, string $var)
	{
		$stack = new Stack();
		$output = new Queue();
		
		$lexer = new Lexer($expression);
		
		$lastToken = NULL;
		foreach($lexer as $token)
		{
			switch($token->Type)
			{
				case "Number":
					$output->Enqueue($token);
					break;

				case "Identifier":
					switch($token->Value)
					{
						case $var:
						case "Pi":
						case "E":
							$output->Enqueue(new Token("Variable", $token->Value));
							break;

						default:
							$stack->Push($token);
							break;
					}
					
					break;

				case "Comma":
					$mismatch = false;
					while(!($mismatch = $stack->IsEmpty()) && $stack->Top()->Type != "LeftParenthesis")
					{
						$output->Enqueue($stack->Pop());
					}

					if($mismatch)
						throw new Exception("Mismatched parentheses");

					break;

				case "Plus":
				case "Minus":
				case "Multiplication":
				case "Division":
				case "Modulo":
				case "Exponentiation":

					while(!$stack->IsEmpty() && Calculator::is_operator(($op = $stack->Top())))
					{
						$associativity_t = Calculator::is_left_associative($token);
						$precedence_t = Calculator::get_precedence($token);
						$precedence_op = Calculator::get_precedence($op);

						if(($associativity_t && $precedence_t <= $precedence_op) 
							|| (!$associativity_t && $precedence_t < $precedence_op))
						{
							$output->Enqueue($stack->Pop());
						}
						else
						{
							break;
						}
					}

					$stack->Push($token);
					break;

				case "LeftParenthesis":
					$stack->Push($token);
					break;

				case "RightParenthesis":
					
					while(!$stack->IsEmpty() && $stack->Top()->Type != "LeftParenthesis")
					{
						$output->Enqueue($stack->Pop());
					}

					if($stack->IsEmpty())
						throw new Exception("Mismatched parentheses");

					$stack->Pop();

					if(!$stack->IsEmpty() && $stack->Top()->Type == "Identifier")
						$output->Enqueue($stack->Pop());

					break;				
			}

			$lastToken = $token;
		}

		while(!$stack->IsEmpty())
		{
			$top = $stack->Top();
			if($top->Type == "LeftParenthesis" || $top->Type == "RightParenthesis")
				throw new Exception("Mismatched parentheses");

			$output->Enqueue($stack->Pop());
		}

		$res = array();
		while(!$output->IsEmpty())
		{
			$res[] = $output->Dequeue();
		}

		return new Expression($res, $var);
	}
	
	private static function is_operator($value)
	{
		switch($value->Type)
		{
			case "Plus":
			case "Minus":
			case "Multiplication":
			case "Division":
			case "Modulo":
			case "Exponentiation":
				return true;
				
			default:
				return false;
		}
	}
		
	private static function is_left_associative($operator)
	{
		switch($operator->Type)
		{
			case "Exponentiation":
				return false;
			case "Plus":
			case "Minus":
			case "Multiplication":
			case "Division":
			case "Modulo":
				return true;

			default:
				throw new Exception("Argument is not an operator");
		}
	}

	private static function get_precedence($operator)
	{
		switch($operator->Type)
		{
			case "Plus":
			case "Minus":;
				return 1;
			case "Multiplication":
			case "Division":
			case "Modulo":
				return 2;
			case "Exponentiation":
				return 3;

			default:
				throw new Exception("Argument is not an operator");
		}
	}
}

?>