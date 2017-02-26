<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<style type="text/css">
		.graph
		{
			width: 500px;
			height: 500px;
		}

		.graph > line
		{ 
			stroke: black;
			stroke-width: 1;
		}
		.graph > path
		{
			stroke: red;
			fill: none;
			stroke-width: 2;
		}
	</style>
</head>
<body>
	<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
		&#x1d453; ( <input type="text" name="var" required value="<?php if(isset($_POST["var"])) echo $_POST["var"]; ?>" /> ) = 
		<input type="text" name="expr" required value="<?php if(isset($_POST["expr"])) echo $_POST["expr"]; ?>" />
		<br />
		<br />
		<label for="low">Lower bound: </label><input type="number" step="0.001" name="low" id="low" required value="<?php if(isset($_POST["low"])) echo $_POST["low"]; ?>" />
		<label for="high">Upper bound: </label><input type="number" step="0.001" name="high" id="high" required value="<?php if(isset($_POST["high"])) echo $_POST["high"]; ?>" />
		<input type="submit" name="submit" value="Go" />
	</form>
	<br />

	<?php

		require_once "../src/Calculator.php";

		class Point
		{
			public $x;
			public $y;

			public function __construct(float $x, float $y)
			{
				$this->x = $x;
				$this->y = $y;
			}
		}

		function drawPath(array $points)
		{
			echo '<path vector-effect="non-scaling-stroke" d="';
			for($i = 0; $i < count($points); $i++)
			{
				$p = $points[$i];

				if($i == 0)
				{
					echo "M";
				}
				else
				{
					echo "L";
				}

				echo $p->x . "," . -$p->y . " ";
			}
			echo '" />';
		}

		function drawLine(Point $start, Point $end)
		{
			echo '<line vector-effect="non-scaling-stroke" ';
			echo 'x1="' . $start->x . '" x2="' . $end->x . '" ';
			echo 'y1="' . -$start->y . '" y2="' . -$end->y . '" />';
		}

		if(isset($_POST["expr"]))
		{
			$expr = $_POST["expr"];
			$var = $_POST["var"];
			$minX = $_POST["low"];
			$maxX = $_POST["high"];

			$compiledExpression = Calculator::CompileExpression($expr, $var);

			$points = array();
			foreach(range($minX, $maxX, ($maxX - $minX) / 500) as $xVal)
			{
				$result = Calculator::EvaluateCompiledExpression($compiledExpression, $xVal);
				
				if(is_finite($result))
					$points[] = new Point($xVal, floatval($result));
			}

			function getY(Point $p)
			{
				return $p->y;
			}

			$minY = min(array_map("getY", $points));
			$maxY = max(array_map("getY", $points));
			$height = 1.05 * ($maxY - $minY);
			$width = 1.05 * ($maxX - $minX);

			$viewY = -($minY + $maxY + $height) / 2;
			$viewX = ($minX + $maxX - $width) / 2;

			echo "Postfix: $compiledExpression";

			?>
				<div style="width:1000px;height:1000px">
				<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none" class="graph" viewbox="<?php echo "$viewX $viewY $width $height"; ?>">
			<?php

			drawLine(new Point($viewX, 0), new Point($viewX + $width, 0));
			drawLine(new Point(0, -$viewY), new Point(0, -$viewY - $height));

			drawPath($points);

			echo '</svg></div>';
		}

	?>
</body>
</html>