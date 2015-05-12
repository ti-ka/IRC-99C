<?php

    if(isset($_POST["name"]) && isset($_POST["answers"]) && isset($_POST["wNr"]) && isset($_POST["quizId"]) ){
        $quizId = $_POST["quizId"];
        $name = $_POST["name"];
        $wNr = $_POST["wNr"];
        $answers = $_POST["answers"];

        require_once "classes.php";
        $quiz = new Quiz($quizId);
        $save = $quiz->save($name,$wNr,$answers);

        if($save){
            echo "Successfully Saved";
        } else {
            echo "Failed to Save";
            //print_r($quiz->errors);
        }
    } else {
        echo "Failed to Save. Invalid Parameters";
    }

/*
if(isset($_GET["name"]) && isset($_GET["answers"]) && isset($_GET["quizId"]) ){


    echo $quizId = $_GET["quizId"];
    echo $name = $_GET["name"];
    echo $answers = $_GET["answers"];

    require_once "classes.php";
    $quiz = new Quiz($quizId);
    $save = $quiz->save($name,$answers);

    if($save){
        echo "Successfully Saved";
    } else {
        echo "Failed to Save";
    }
} else {
    echo "Invalid Parameters";
}
*/
