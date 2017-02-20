<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Calculator</title>
    </head>
    <body>
        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="GET">
            &#x1d453; (
            <input type="text" name="var"
                <?php if(isset($_GET["var"])) echo 'value="' . $_GET["var"] . '"'; ?>
            /> ) = 
            <input type="text" name="expr" 
                <?php if(isset($_GET["expr"])) echo 'value="' . $_GET["expr"] . '"'; ?>
            />
            <input type="submit" value="Go" />
        </form>

        <?php

            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            if(isset($_GET["expr"])){
                $expr = $_GET["expr"];
                $var = $_GET["var"];

                require_once "../src/Calculator.php";

                $compiledExpression = Calculator::CompileExpression($expr, $var);

                echo "<br />";
                echo "Infix: $expr<br />";
                echo "Postfix: $compiledExpression<br />";
                echo '<table border="1">';
                echo "<tr><th>Calculation</th><th>Result</th></tr>";
                foreach(range(-10, 10, 0.25) as $val)
                {
                    $result = Calculator::EvaluateCompiledExpression($compiledExpression, $val);

                    echo "<tr><td>f($val)</td><td> = <b>$result</b></td></tr>";
                }
                echo "</table>";
        ?>

        <?php } ?>
    </body>
</html>