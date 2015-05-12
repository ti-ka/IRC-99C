<?php
    session_start();

    if(isset($_GET["success"]) && $_GET["success"] == "true"){
        $_SESSION["message"] = "Successfully Submitted.";
        //header("Location:index.php");
    }

    if(isset($_SESSION["message"])){
        echo '
            <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="text-center">Done!</h4>
                <p class="text-center">'.$_SESSION["message"].'</p>
            </div>
        ';
        unset($_SESSION["message"]);
    }
?>
<!DOCTYPE HTML>
<head>
<head>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="styles/cosmo.css">
    <link rel="stylesheet" href="styles/custom.css">
    <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
    <?php $title = (isset($_GET['quiz'])) ?  "Quiz ".$_GET['quiz']  : 'Electronic Scantron' ?>
    <title><?php echo $title ?></title>
</head>
<body>
<div class="container" id="scantron">
<h1 class="text-center">Electronic Scantron</h1>
<?php
    require_once "classes.php";
    if(isset($_GET['quiz'])){
        $quizId = strtoupper($_GET['quiz']);
        $quiz = new Quiz($quizId);
        $totalQuestions = $quiz->questionsCount;
        if($totalQuestions == 0){
            echo '
            <div class="alert alert-dismissible alert-warning">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="text-center">Invalid Quiz</h4>
                <p class="text-center">Please Enter a valid Quiz ID</p>
            </div>
            <div class="clearfix">
                <form class="text-center" method="get" action="index.php">
                    <input type="text" class="form-control" name="quiz" placeholder="Enter Quiz ID">
                    <button class="btn btn-success"">Start</button>
                </form>
            </div>';
            exit();
        }
        //Loading Scantron
        $questionNr = 1;
        while($questionNr <= $totalQuestions){
            $question = new Scantron($questionNr,5);
            echo $question->display();
            $questionNr++;
        }
        echo '
            <div class="clearfix">
                <h3>And finally,</h3>
                <input type="text" class="form-control" id="name" placeholder="Your Last Name">
                <input type="text" class="form-control" id="wNr" placeholder="Your ID">
                <button class="btn btn-primary" id="save">Submit</button>
            </div>';
    } else {
        echo '
            <div class="clearfix">
                <form class="text-center" method="get" action="index.php">
                    <input type="text" class="form-control" name="quiz" placeholder="Enter Quiz ID">
                    <button class="btn btn-success"">Start</button>
                </form>
            </div>';
        exit();
    }
    ?>
</div>
<script>
    var totalQuestions = <?php echo $totalQuestions ?>;
    var quizId = "<?php echo $quizId ?>";
    var ANSWERS = {};
    var scantron = $('#scantron');

    scantron.on('click','.answer',function(){
        var $questionNr = $(this).data('question');
        var $answerIndex = $(this).data('answer');
        var $answerType = $(this).data('type');

        if($answerType == 'mcq'){
            ANSWERS[$questionNr] = $answerIndex;
            $(this).siblings().removeClass('checked');
            $(this).addClass('checked');
        }

        if($answerType == 'ma') {
            var $oldAnswer = ANSWERS[$questionNr] || '';
            if ($oldAnswer.indexOf($answerIndex) == -1) {
                ANSWERS[$questionNr] = $oldAnswer + '' + $answerIndex;
            } else {
                ANSWERS[$questionNr] = ANSWERS[$questionNr].replace($answerIndex, '');
            }
            $(this).toggleClass('checked');
        }

        console.clear();
        console.log(ANSWERS);

    });

    scantron.on('click','.question-nr',function(){
        $(this).parent('.answers').siblings('.question').css('visibility','visible');
    });
/*
    scantron.on('click','.question',function(){
        $(this).css('visibility','hidden');
    });
*/
    scantron.on('click','#save',function(){
        var save = true;
        var name = $('#name').val();
        var wNr = $('#wNr').val();
        if(name == '' || wNr == ''){
            alert('Please Enter your name or W#');
            save = false;
        } else {
            for(var i = 1; i <= totalQuestions; i++){
                if(!ANSWERS[i]){
                    alert('Please select an answer for Question #'+i);
                    save = false;
                    break;
                }
            }
        }
        if(save){
            //Disabling to prevent further saving
            $(this).prop('disabled', true);
            var alpha = ["", "A","B","C","D","E"];
            var ansArray = $.map(ANSWERS, function(value, index) {
                return alpha[value];
            });

            var ans = ansArray.join("");
            console.log(name);
            console.log(ans);
            console.log(quizId);

            var jqxhr = $.ajax({
                method: "POST",
                url: "save.php",
                data: { name: name, wNr  : wNr,  answers : ans, quizId : quizId  }
            });

            jqxhr.done(function(msg) {
                if(msg == "Successfully Saved"){
                    document.location.href = "index.php?success=true";
                } else {
                    alert( "Error : " + msg);
                    $("#save").prop('disabled', false);  //Unlock
                }
            });
            jqxhr.fail(function() {
                alert( "Error Sumbitting." );
                $("#save").prop('disabled', false);  //Unlock
            });
        }

    });
</script>
</body>
</html>