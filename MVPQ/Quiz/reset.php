<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="refresh" content="0; url=/Quiz/quiz.php">

<title>Eric's Quiz</title>
<LINK REL="stylesheet" TYPE="text/css" HREF="/images/quiz.css" />
</head>

<body>
<h2>Eric's Quiz</h2>
<h3>Reset</h3>
<?php
//Process previous answer
   $group = $_SESSION['group'];
   $language = $_SESSION['language'];
   
   switch($language) {
   case "Bahasa": $sqlite="sqlite:quizBahasa.sqlite3"; break;
   case "Dutch": $sqlite="sqlite:quizDutch.sqlite3"; break;
   case "French": $sqlite="sqlite:quizFrench.sqlite3"; break;
   case "Italian": $sqlite="sqlite:quizItalian.sqlite3"; break;
   case "Spanish": $sqlite="sqlite:quizSpanish.sqlite3"; break;
   case "Swahili": $sqlite="sqlite:quizSwahili.sqlite3"; break;
   default: $sqlite="";
   }

   try{
       $handle = new PDO($sqlite);
       $sql = sprintf("update Quiz SET ex_attempts = 0.0000000, ex_corrects = 0.00 WHERE \"group\" = '%s'", $_SESSION['group']);
       $update = $handle->prepare($sql);
       $update->execute();
   } catch (PDOException $e) {
         print "Error!: " . $e->getMessage() . "<br/>";
         die();
    }
    $_SESSION["min"] = 0;

    printf("<A HREF='/Quiz/quiz.php?group=%s&language=%s'>"
		."Click </A> return to English<br>\n", 
		$group, $language);
?>

</body>
</html> 

