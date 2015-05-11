<link rel="stylesheet" href="styles/cosmo.css">
<link rel="stylesheet" href="styles/custom.css">
<script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
<div class="container" id="scantron">
    <h1 class="text-center">Electronic Scantron</h1>
<?php
    require_once "classes.php";
    if(isset($_GET['quiz'])){
        $quizId = $_GET['quiz'];
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
                <input type="text" class="form-control" id="name" placeholder="Your Name or W#">
                <button class="btn btn-warning" id="save">Submit</button>
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
        if(name == ''){
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
                data: { name: name, answers : ans, quizId : quizId  }
            });

            jqxhr.done(function(msg) {
                if(msg != "Successfully Saved"){
                    $("#save").prop('disabled', false);  //Unlock
                }
                alert( msg );
            });
            jqxhr.fail(function() {
                alert( "Error Sumbitting." );
            });
        }

    });
</script>