<?php
    $a = 10;
    $b = 20;

    $c = $b;
    $b = $a;
    $a = $c;
    var_dump($a, $b);
?>