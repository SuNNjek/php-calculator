<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Bisection</title>
    </head>
    <body>
        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
            &#x1d453; (
            <input type="text" name="var" required
                <?php if(isset($_POST["var"])) echo 'value="' . $_POST["var"] . '"'; ?>
            /> ) = 
            <input type="text" name="expr" required
                <?php if(isset($_POST["expr"])) echo 'value="' . $_POST["expr"] . '"'; ?>
            />
			<br />
			<br />
			Lower bound: <input type="number" step=".0000001" name="low" required
                <?php if(isset($_POST["low"])) echo 'value="' . $_POST["low"] . '"'; ?>
			/>
			Upper bound: <input type="number" step=".0000001" name="high" required
                <?php if(isset($_POST["high"])) echo 'value="' . $_POST["high"] . '"'; ?>
			/>
			Precision: <input type="number" step=".0000001" name="precision"
                <?php if(isset($_POST["precision"])) echo 'value="' . $_POST["precision"] . '"'; ?>
			/>
			<br />
			<br />
            <input type="submit" value="Go" name="submit" /calc.php?var=x&expr=ln(2*x)>
        </form>

        <?php

            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            if(isset($_POST["submit"]))
			{
                require_once "../src/Calculator.php";

                function Bisection(Expression $expr, float $lowX, float $highX, float $precision, int $iteration = 1)
				{
					$lowVal = Calculator::EvaluateCompiledExpression($expr, $lowX);
					$highVal = Calculator::EvaluateCompiledExpression($expr, $highX);

					//Is one of the values already 0?
					if($lowVal == 0)
					{
						return $lowX;
					}
					else if($highVal == 0)
					{
						return $highX;
					}

					if($lowVal > 0 xor $highVal > 0)
					{
						if($highX - $lowX < $precision)
							return ($highX + $lowX) / 2;

						$midX = ($lowX + $highX) / 2;
						$midVal = Calculator::EvaluateCompiledExpression($expr, ($lowX + $highX) / 2);

						echo "<tr>";
						echo "<td>$iteration</td>";
						echo "<td>". number_format($lowX, 6) ."</td><td>". number_format($lowVal, 6) ."</td>";
						echo "<td>". number_format($midX, 6) ."</td><td>". number_format($midVal, 6) ."</td>";
						echo "<td>". number_format($highX, 6) ."</td><td>". number_format($highVal, 6) ."</td>";
						
						
						if($lowVal > 0 xor $midVal > 0)
						{
							echo "<td>[a; b]</td>";
							echo "</tr>";

							return Bisection($expr, $lowX, $midX, $precision, ++$iteration);
						}
						else
						{
							echo "<td>[b; c]</td>";
							echo "</tr>";
							
							return Bisection($expr, $midX, $highX, $precision, ++$iteration);
						}
					}
					else
					{
						return NULL;
					}
				}
				
				$expr = $_POST["expr"];
				$var = $_POST["var"];
				$low = floatval($_POST["low"]);
				$high = floatval($_POST["high"]);
				$precision = max(floatval($_POST["precision"]), 0.0000001);

				$compiledExpression = Calculator::CompileExpression($expr, $var);

				?>
				<br />
				<table border="1" width="100%" style="text-align:center">
					<tr>
						<th>Iteration</th>
						<th>a</th>
						<th>f(a)</th>
						<th>b</th>
						<th>f(b)</th>
						<th>c</th>
						<th>f(c)</th>
						<th>Root is in<br />new interval</th>
					</tr>
				<?php
					$zero = Bisection($compiledExpression, $low, $high, $precision);
				?>
				</table>
				<?php

				echo "<br />The root is around X = " . round($zero, (int)(-log($precision, 10))) . " with a precision of $precision.";
			}
		?>
    </body>
</html>