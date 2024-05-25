<?php

    require_once './zxcvbn.php';

    $score = p('i dont care');

    echo '<h1>score: '.$score[0].' <br> warning: '.$score[1].'<br>'.$score[2][0].' </h1>';

    $score = p($password);
    $msg2 = $score[1].'<br>'.$score[2];
    if ($score[0] <3) {
        $b = false;
    }