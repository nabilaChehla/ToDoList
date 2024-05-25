<?php

    require_once './password/Feedback.php';
    require_once './password/Matcher.php';
    require_once './password/Scorer.php';
    require_once './password/TimeEstimator.php';
    require_once './password/Zxcvbn.php';
    require_once './password/Matchers/BaseMatch.php';
    require_once './password/Matchers/Bruteforce.php';
    require_once './password/Matchers/DateMatch.php';
    require_once './password/Matchers/DictionaryMatch.php';
    require_once './password/Matchers/L33tMatch.php';
    require_once './password/Matchers/RepeatMatch.php';
    require_once './password/Matchers/SpatialMatch.php';
    require_once './password/Matchers/YearMatch.php';
    require_once './password/Matchers/ReverseDictionaryMatch.php';
    require_once './password/Matchers/SequenceMatch.php';

    use ZxcvbnPhp\Zxcvbn;
    function p($password){
        $zxcvbn = new Zxcvbn();
        $userData = [];
        $weak = $zxcvbn->passwordStrength($password, $userData);
        return array($weak['score'],$weak['feedback']['warning'],$weak['feedback']['suggestions']); 
    }


    