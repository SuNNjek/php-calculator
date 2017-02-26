<?php

	class Lexer implements Iterator
	{
		private $code;
		private $index = 0;
		private $lastIndex = 0;
		private $length;

		private $curr = NULL;

		public function __construct(string $code)
		{
			$this->code = trim($code);
			$this->length = strlen($this->code);
		}

		//<Iterator implementation>

		public function current()
		{
			$tmpIndex = $this->index;
			$res = $this->ConsumeToken();
			$this->index = $tmpIndex;

			return $res;
		}

		public function key()
		{
			return $this->lastIndex;
		}

		public function next()
		{
			$res = $this->ConsumeToken();
			$this->lastIndex = $this->index;
			return $res;
		}

		public function rewind()
		{
			$this->index = 0;
		}

		public function valid()
		{
			return $this->index < $this->length;
		}

		//</Iterator implementation>

		private function ConsumeToken()
		{
			$this->ConsumeWhitespace();

			if(($token = $this->ConsumeOperator()) != NULL)
				return $token;

			if(($token = $this->ConsumeNumber()) != NULL)
				return $token;

			if(($token = $this->ConsumeComma()) != NULL)
				return $token;

			if(($token = $this->ConsumeParentheses()) != NULL)
				return $token;

			if(($token = $this->ConsumeIdentifier()) != NULL)
				return $token;

			throw new Exception("Invalid token at position " . $this->index . ": " . substr($this->code, $this->index, 10));
		}

		private function Peek()
		{
			if($this->index >= $this->length)
				return NULL;

			return $this->code[$this->index];
		}

		private function Consume()
		{
			if($this->index >= $this->length)
				return NULL;

			return $this->code[$this->index++];
		}

		private function ConsumeWhitespace()
		{
			while(ctype_space($this->Peek()))
				$this->Consume();
		}

		private function ConsumeIdentifier()
		{
			$ident = "";

			if($this->Peek() != "_" && !ctype_alpha($this->Peek()))
				return NULL;

			$ident .= $this->Consume();

			while(ctype_alnum($this->Peek()) || $this->Peek() == "_")
				$ident .= $this->Consume();

			return new Token("Identifier", $ident);
		}

		private function ConsumeOperator()
		{
			switch($this->Peek())
			{
				case "+":
					return new Token("Plus", $this->Consume());
				case "-":
					return new Token("Minus", $this->Consume());
				case "*":
					return new Token("Multiplication", $this->Consume());
				case "/":
					return new Token("Division", $this->Consume());
				case "%":
					return new Token("Modulo", $this->Consume());
				case "^":
					return new Token("Exponentiation", $this->Consume());

				default:
					return NULL;
			}
		}

		private function ConsumeParentheses()
		{
			switch($this->Peek())
			{
				case "(":
					return new Token("LeftParenthesis", $this->Consume());
				case ")":
					return new Token("RightParenthesis", $this->Consume());

				default:
					return NULL;
			}
		}

		private function ConsumeComma()
		{
			if($this->Peek() == ",")
			{
				return new Token("Comma", $this->Consume());
			}

			return NULL;
		}

		private function ConsumeNumber()
		{
			$res = "";

			$peek = $this->Peek();
			if(!ctype_digit($peek) && $peek != ".")
				return NULL;

			if(ctype_digit($this->Peek()))
				$res .= $this->ConsumeInteger();

			//Lex decimal places, if it exists
			if($this->Peek() == ".")
			{
				$res .= $this->Consume();

				if(ctype_digit($this->Peek()))
					$res .= $this->ConsumeInteger();
			}

			//Lex scientific format
			if($this->Peek() != "E" && $this->Peek() != "e")
				return new Token("Number", $res);

			$res .= $this->Consume();

			if($this->is_sign($this->Peek()))
				$res .= $this->Consume();

			if(ctype_digit($this->Peek()))
				$res .= $this->ConsumeInteger();
			else
				throw new Exception("Invalid number format");

			return new Token("Number", $res);
		}

		private function ConsumeInteger()
		{
			$res = "";

			if(!ctype_digit($this->Peek()))
				return NULL;

			while(ctype_digit($this->Peek()))
				$res .= $this->Consume();

			return $res;
		}

		private function is_sign($value)
		{
			return $value == "+" || $value == "-";
		}
	}

	class Token
	{
		public $Type;
		public $Value;

		public function __construct($type, $value)
		{
			$this->Type = $type;
			$this->Value = $value;
		}

		public function __toString()
		{
			return "[" . $this->Type . "]: " . $this->Value;
		}
	}

?>