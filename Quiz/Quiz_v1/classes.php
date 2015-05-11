<?php
class Question{
    public $questionNr;
    public $question;
    public $answerCount = 0;
    public $answers = array();
    public $correctAnswer;
    public $answerType;

    public function __construct($questionNr,$rawQuestion){
        $this->questionNr = $questionNr;
        $this->question = $rawQuestion->question;
        $this->answers = (array) $rawQuestion->answers;
        if(isset($rawQuestion->correctAnswer)){
            $this->correctAnswer = $rawQuestion->correctAnswer;
        } else {
            $this->correctAnswer = $this->answers[0];
        }
        if(isset($rawQuestion->answerType)){
            $this->answerType = $rawQuestion->answerType;
        } else {
            $this->answerType = 'mcq';
        }

    }

    public function isRight($answer){
        if($answer == $this->correctAnswer){
            return true;
        }
        return false;
    }

    public function display(){
        $str = '<div class="question-set">';
        $str .= '<div class="col-xs-12 question">'.$this->question.'</div>';
        $str .= '<div class="col-xs-12 answers">';
        $str .= '<div class="col-xs-4 question-nr">'.$this->questionNr.'</div>';
        $str .= '<div class="col-xs-8">';
        $answerIndex = 1;
        foreach($this->answers as $answer){
            $str .= '<div class="col-xs-3 answer"
                data-type="'.$this->answerType.'"
                data-question="'.$this->questionNr.'"
                data-answer="'.$answerIndex.'">'.
                $answer.'</div>';
            $answerIndex++;
        }
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';
        return $str;
    }

}
class Scantron{
    public $answerCount;
    public $questionNr;
    public $answer;
    public $correctAnswer;
    public $answerIndex = array('','A','B','C','D','E','F','G','H','I','J');

    public function __construct($questionNr,$answerCount = 4,$correctAnswer = 0){
        $this->questionNr = $questionNr;
        $this->answerCount = $answerCount;
        $this->correctAnswer = $correctAnswer;
        $this->answerType = 'mcq';
    }

    public function isRight($answer){
        if($answer == $this->correctAnswer){
            return true;
        }
        return false;
    }

    public function display(){
        $str = '<div class="question-set">';
        //$str .= '<div class="col-xs-12 question">'.$this->question.'</div>';
        $str .= '<div class="col-xs-12 answers">';
        $str .= '<div class="col-xs-4 question-nr">'.$this->questionNr.'</div>';
        $str .= '<div class="col-xs-8">';

        for($i = 1; $i <= $this->answerCount; $i++){
            $str .= '<div class="answer"
                data-type="'.$this->answerType.'"
                data-question="'.$this->questionNr.'"
                data-answer="'.$i.'">'. $this->answerIndex[$i].
                '</div>';
        }

        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';
        return $str;
    }
}


class Quiz{
    public $quizId;
    public  $questionsCount = 0;
    private $correctAnswers = array();
    private $saveTo;
    public  $errors = [];

    public function __construct($quizId)
    {
        try{
            $data = file_get_contents("javascripts/quizdata.json");
            $quizzes = (array) json_decode($data);

            if(isset($quizzes[$quizId])){
                $thisQuiz = (array) $quizzes[$quizId];

                $this->quizId = $quizId;
                $this->questionsCount = $thisQuiz["questionsCount"];
                $this->correctAnswers = str_split(strtoupper($thisQuiz["correctAnswers"]));
                $this->saveTo = "answers/".$quizId.".txt";

            }
        } catch (Exception $e){
            $this->errors[] = "Invalid Quiz";
        }

    }

    /*
     * @args : (string) of answers
     * @action: compares with correct answer
     * @return : (int) number of correct anser
     */

    private function countCorrectAnswers($check){
        $correct = 0;
        $checkArray = str_split($check);

        //var_dump($checkArray);
        //var_dump($this->correctAnswers);

        if(count($checkArray) != count($this->correctAnswers)){
            $this->errors[] = "Invalid Number of answers to check";
            return false;
        } else {
            for($i = 0; $i < count($checkArray); $i++){

                if($checkArray[$i] == $this->correctAnswers[$i]){
                    $correct++;
                }
            }
        }

        return $correct;

    }


    private function binaryCorrectAnswers($check){
        $bin = "";
        $checkArray = str_split($check);


        if(count($checkArray) != count($this->correctAnswers)){
            $this->errors[] = "Invalid Number of answers to check";
            return false;
        } else {
            for($i = 0; $i < count($checkArray); $i++){

                if($checkArray[$i] == $this->correctAnswers[$i]){
                    $bin .= 1;
                } else {
                    $bin .= 0;
                }
            }
        }

        return $bin;

    }


    public function save($name,$answers){
        $time       = new DateTime('now');
        try{
            $data = @file_get_contents($this->saveTo);
            if($data == ""){
                $data .= "Date/Time\tName/Id\tCorrect\tAnswers\tBinary Answers";
            }

            $correctCount = $this->countCorrectAnswers($answers);
            $binaryAnswers = $this->binaryCorrectAnswers($answers);

            if($correctCount && $name != ""){
                $toPut  = $data;
                $toPut .= "\n".$time->format("m/d H:i");
                $toPut .= "\t".$name;
                $toPut .= "\t".$correctCount;
                $toPut .= "\t".$answers;
                $toPut .= "\t".$binaryAnswers;

                if(file_put_contents($this->saveTo, $toPut)){
                    return true;
                } else {
                    $this->errors[] = "Saving Failed";
                    return false;
                }
            } else {
                $this->errors[] = "Name/Answers Invalid";
            }



        } catch (Exception $e){
            $this->errors[] = "Fetching File Failed";
            return false;
        }
    }

}
/*
    $questions = json_decode(file_get_contents('javascripts/data.json'));
    $questionNr = 1;
    foreach($questions as $rawQuestion){
        $question = new Question($questionNr,$rawQuestion);
        echo $question->display();
        $questionNr++;
    }
 */



?>