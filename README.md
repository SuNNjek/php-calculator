# php-calculator
A function calculator for PHP using the shunting yard algorithm

## How to use
To evaluate a function you use the `Calculator` class in src/Calculator.php

If you just want to evaluate a function once you can use the following function:

```php
float Calculator::EvaluateExpression(string $expression, string $var, float $value);
```

This function parses the expression and exchanges the identifier given with `$var` for `$value`. 

If you want to evaluate the same function but for a lot of different values, it's better to "compile" it to postfix notation first.
This can be done with the function:

```php
Expression Calculator::CompileExpression(string $expression, string $var);
```

This returns an `Expression` object which contains the expression's tokens in postfix order and the name of the variable.

To evaluate the `Expression` object with a given value, you use the function
```php
float Calculator::EvaluateCompiledExpression(Expression $expression, float $value);
```
which returns the value of the expression for the given value.