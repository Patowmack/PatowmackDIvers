<?php
header('Content-Type: text/html; charset=UTF-8'); ?>
<?php
session_start(); ?>
<!DOCTYPE html>

<HTML>

<HEAD>
<!-- Beautify: http://phpbeautifier.com/beautify.php -->
<?php
if (get_magic_quotes_gpc())
{
   $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST
   );
   while (list($key, $val) = each($process))
   {
      foreach($val as $k => $v)
      {
         unset($process[$key][$k]);
         if (is_array($v))
         {
            $process[$key][stripslashes($k) ] = $v;
            $process[] = & $process[$key][stripslashes($k) ];
         }
         else
         {
            $process[$key][stripslashes($k) ] = stripslashes($v);
         }
      }
   }

   unset($process);
}

function languageSetUp()
{

   //   error_reporting(E_ALL ^ E_NOTICE);

   $language = (isset($_GET['language']) > 0) ? $_GET['language'] : $_SESSION['language'];
   $_SESSION['language'] = $language;
   switch ($language)
   {
   case "Bahasa":
      $database = "SQLite3:quizBahasa.sqlite3";
      $keyboard = "KB_Bahasa.png";
      break;

   case "Dutch":
      $database = "SQLite3:quizDutch.sqlite3";
      $keyboard = "KB_Dutch.png";
      break;

   case "French":
      $database = "SQLite3:quizFrench.sqlite3";
      $keyboard = "KB_French.png";
      break;

   case "Italian":
      $database = "SQLite3:quizItalian.sqlite3";
      $keyboard = "KB_Italian.png";
      break;

   case "Spanish":
      $database = "SQLite3:quizSpanish.sqlite3";
      $keyboard = "KB_Spanish.png";
      break;

   case "Swahili":
      $database = "SQLite3:quizSwahili.sqlite3";
      $keyboard = "KB_Swahili.png";
      $keyboard = "";
      break;

   default:
      $_SESSION['language'] = "";
      $database = "";
      $keyboard = "";
   }

   return array(
      $language,
      $database,
      $keyboard
   );
}

//
// Score and display answer from previous screen
//

function firstInGroup($ans, $xOther, $english, &$ex_corrects, &$ex_attempts, &$today_corrects, &$today_attempts, &$id, $handle)
{
   $ans_safe = htmlspecialchars($ans, ENT_QUOTES | ENT_HTML401, 'UTF-8');
   if (strtolower(trim($ans)) == strtolower(trim($xOther)))
   { // correct
      printf("<FONT COLOR='green'>Correct, your answer was <I>%s</I>", $ans);
      printf(" (%1.0f/%1.0f = %2.0f%%)</FONT><br />\n", ++$ex_corrects, ++$ex_attempts, $ex_attempts > 0 ? $ex_corrects / $ex_attempts * 100 : 0);
      $_SESSION['ans_previous'] = '';
      $_SESSION['today_corrects'] = ++$today_corrects;
      $_SESSION['today_attempts'] = ++$today_attempts;
   }
   else
   if (strlen(trim($ans)) > 0)
   { // wrong and NOT RESET
      echo "<FONT COLOR='red'>Error <I><FONT COLOR='green'>|$english|</FONT><FONT COLOR='red'></I> is not <I>|$ans|</I></FONT><br />";
      echo "The answer is  <I><B>|$xOther|</B></I>";
      printf(" (%1.0f/%1.0f = %2.0f%%)</FONT><br />\n", $ex_corrects, ++$ex_attempts, $ex_attempts > 0 ? $ex_corrects / $ex_attempts * 100 : 0);
      $_SESSION['ans_previous'] = $xOther;
      $_SESSION['today_attempts'] = ++$today_attempts;
   }

   //
   // Record score
   //

   try
   {
      $updateSQL = 'UPDATE quiz SET ex_attempts = ?, ex_corrects = ?, "date" = CURRENT_TIMESTAMP WHERE id = ?';
      $update = $handle->prepare($updateSQL);
      $update->bindParam(1, $ex_attempts);
      $update->bindParam(2, $ex_corrects);
      $update->bindParam(3, $_SESSION['id']);
      if (strlen($ans) > 0)
      { // ANSWERED
         $update->execute();
      }
   }

   catch(PDOException $e)
   {
      print "Error!: " . $e->getMessage() . "<br />";
      die();
   }
} // firstInGroup
list($language, $database, $keyboard) = languageSetUp();

//

printf("<TITLE>%s</TITLE>", $language);
printf("<LINK REL=stylesheet TYPE=text/css HREF=../images/quiz.css /> ");
printf("<LINK REL=icon type=image/png HREF=../images/favicon.ico>\n");
printf("</HEAD>");
printf('<BODY onLoad = "document.question.ans.focus()" >');
printf("<H2><A HREF=/Quiz/language.php>Eric's English->%s Quiz</a></H2>\n", $language);
?>

<!-- <?php
include 'navsite.php';
 ?> -->

<DIV ID="container">
   
<?php

if ($language == "")
{
   printf("<A HREF=/Quiz/quiz.php?language=Bahasa>Bahasa</a><br />");
   printf("<A HREF=/Quiz/quiz.php?language=Dutch>Dutch</a><br />");
   printf("<A HREF=/Quiz/quiz.php?language=French>French</a><br />");
   printf("<A HREF=/Quiz/quiz.php?language=Italian>Italian</a><br />");
   printf("<A HREF=/Quiz/quiz.php?language=Spanish>Spanish</a><br />");
   printf("<A HREF=/Quiz/quiz.php?language=Swahili>Swahili</a><br />");
}

$SUCCESS = 80.0; // Time to get new set of words from group
$zeroOut = 0;
$firstInSubGroup = 0;
date_default_timezone_set('America/New_York');
try
{
   $handle = new PDO($database); // INVALID DATA SOURCE
}

catch(PDOException $e)
{
   printf("<br />Error:  $database<br />");
}

//
// $firstInGroup ==1 means new group of words from bottom of screen
// $firstInSubGroup == 1 means get a new set of 6 words (see SQL for LIMIT 6)
//
// Process previous answer
//

$_SESSION['id'] = $_SESSION['id'];
$today_corrects = $_SESSION['today_corrects'];
$today_attempts = $_SESSION['today_attempts'];

//
// Get response store from previous screen
//

$group = isset($_GET['group']) ? $_GET['group'] : "";

if (strlen($group) == 0)
{ // process answer
   $group = $_SESSION['group'];
   $ans = isset($_POST['ans']) ? $_POST['ans'] : "";
   $xOther = $_SESSION['xOther'];
   $english = $_SESSION['english'];
   $ex_corrects = $_SESSION['ex_corrects'];
   $ex_attempts = $_SESSION['ex_attempts'];
   $ex = isset($_SESSION['ex']) ? $_SESSION['ex'] : "";
   $ans_previous = $_SESSION['ans_previous'];
   $firstInSubGroup = isset($_SESSION['firstInSubGroup']) ? $_SESSION['firstInSubGroup'] : "";
   $firstInGroup = 0;
}
else
{ // new group
   $firstInGroup = 1;
}

$minPctCorrect = 100.0;
printf("<h3>\n");
printf("Group is %s (%1.0f/%1.0f=%4.0f%%)<br />\n", $group, $today_corrects, $today_attempts, $today_attempts > 0 ? 100.0 * $today_corrects / $today_attempts : 0);

if ($firstInGroup == 0)
{
   firstInGroup($ans, $xOther, $english, $ex_corrects, $ex_attempts, $today_corrects, $today_attempts, $_SESSION['id'], $handle);
}

printf("</h3>\n");

// if ((strlen(trim($_GET['group']))>0) && ($zeroOut == 1)) { //zero out scores

if ((isset($_GET['group']) > 0) && ($zeroOut == 1))
{ //zero out scores
   try
   {
      $sqlZero = sprintf("update Quiz SET ex_attempts = 0.00, ex_corrects = 0.00 WHERE \"group\" = '%s'", $_GET['group']);
      $update = $handle->prepare($sqlZero);
      $update->execute();
   }

   catch(PDOException $e)
   {
      print "Error!: " . $e->getMessage() . "<br />";
      die();
   }
} // group

//
// New set of words
//

$sqlIds = <<<SEL
   SELECT id, "group", xOther, english, ex_attempts, ex_corrects, ex
   FROM vEXQuiz where "group" = ?
   ORDER BY ex/(ex_attempts+0.00001), ex, ex_attempts ASC
   LIMIT 6
SEL;
// ORDER BY score, ex, ex_attempts ASC

if (($firstInSubGroup == 1) || ($firstInGroup == 1))
{ // get new set of words by IDs
   $firstInSubGroup = 0;
   $_SESSION['id'] = "";
   $i = 0;
   $selectIds = $handle->prepare($sqlIds);
   $selectIds->bindParam(1, $group);
   $selectIds->execute();
   $words = "";
   while ($row = $selectIds->fetch())
   {
      $ids.= $row['id'] . ", ";
      $ex = $row['ex'];
      $minPctCorrect = min($minPctCorrect, $ex * 100.0);
      $words.= $row['english'] . sprintf("(%4.0f%%),&nbsp;\n", $ex * 100.0);
   }

   $ids = rtrim($ids, " ,");
   $words = rtrim($words, " ,");
   $_SESSION['ids'] = $ids;
   $_SESSION['words'] = $words;
   printf("<br /><B>Next Group of </B> ");
}

$words = $_SESSION["words"];
$verbs = sprintf('SELECT id, "group", ex_attempts,  ex_corrects, ex, xOther, english FROM vEXQuiz WHERE id IN (%s) LIMIT 6', $_SESSION['ids']);
$s = sprintf('SELECT id, "group", ex_attempts,  ex_corrects, ex, xOther, english FROM vEXQuiz WHERE id IN (%s) ORDER BY score, ex_corrects/ex_attempts ASC LIMIT 6', $_SESSION['ids']);
$s = sprintf('SELECT id, "group", ex_attempts,  ex_corrects, ex, xOther, english FROM vEXQuiz WHERE id IN (%s) ORDER BY ex_corrects /ex_attempts, ex_corrects/ex_attempts ASC LIMIT 6', $_SESSION['ids']);
$s = sprintf('SELECT id, "group", ex_attempts,  ex_corrects, ex, xOther, english FROM vEXQuiz WHERE id IN (%s) ORDER BY (ex_corrects/(ex_attempts+0.00001) +
  (.000000000001 * random() / 92233720368547758070.0) ) , ex_corrects/ex_attempts ASC LIMIT 6', $_SESSION['ids']);
$sorted = sprintf('SELECT id, "group", ex_attempts,  ex_corrects, ex, xOther, english FROM vEXQuiz WHERE id IN (%s) ORDER BY english LIMIT 6', $_SESSION['ids']);

//
// Get words for current group
//

$ids = "'" . $_SESSION['ids'] . "'";
$select = $handle->prepare($s);
$select->execute();
$row = $select->fetch();
$_SESSION['id'] = $row['id'];
$xOther = $row['xOther']; // translate?
$english = $row['english'];
$ex_attempts = $row['ex_attempts'];
$ex_corrects = $row['ex_corrects'];
$ex = $row['ex'];
$words = $row['english'] . sprintf("(%4.0f%%),&nbsp;\n", $ex * 100.0);
$minPctCorrect = min($minPctCorrect, $ex * 100.0);
$select = $handle->prepare($verbs);
$select->execute();
$row = $select->fetch();
$tips = $row['english'] . " (" . $row['xOther'] . ")<br />";

while ($row = $select->fetch())
{
   $tips.= $row['english'] . " (" . $row['xOther'] . ")<br />";
}

$select = $handle->prepare($sorted);
$select->execute();
$row = $select->fetch();
$ex = $row['ex'];
$words = $row['english'] . sprintf("(%4.0f%%),&nbsp;\n", $ex * 100.0);

while ($row = $select->fetch())
{
   $ex = $row['ex'];
   $minPctCorrect = min($minPctCorrect, $ex * 100.0);
   $words.= $row['english'] . sprintf("(%4.0f%%),&nbsp;\n", $ex * 100.0);
}

$words = rtrim($words, " ,");
printf("<A HREF=\"#\" CLASS=\"tooltip\">");

if ($firstInSubGroup)
{
   printf("<br /><B>Next Group of Words</B> %s", $words);
}
else
{
   printf("<B> Words: </B>%s ", $words);
}

printf("<span>$tips</span>");
printf("</a><br />\n");
printf("Current question: <br />English: <B>%s</B><br />", $english);
$_SESSION['id'] = $_SESSION['id'];
$_SESSION['english'] = $english;
$_SESSION['xOther'] = $xOther;
$_SESSION['ex_corrects'] = $ex_corrects;
$_SESSION['ex_attempts'] = $ex_attempts;
$_SESSION['group'] = $group;
$firstInSubGroup = 0;

if ((($minPctCorrect > $SUCCESS)) | ($firstInGroup == 1))
{ // get new set of words by IDs
   $firstInSubGroup = 1;
   $ids = "";
   $i = 0;
   $selectIds = $handle->prepare($sqlIds);
   $selectIds->bindParam(1, $group);
   $selectIds->execute();
   $words = "";
   while ($row = $selectIds->fetch())
   {
      $ids.= $row['id'] . ", ";
      $ex = $row['ex'];
      $minPctCorrect = min($minPctCorrect, $ex * 100.0);
      $words.= $row['english'] . sprintf("(%4.0f%%),&nbsp;\n", $ex * 100.0);
   }

   $ids = rtrim($ids, " ,");
   $words = rtrim($words, " ,");
   $_SESSION['ids'] = $ids;
   $_SESSION['words'] = $words;
} // new set of words
$_SESSION['firstInSubGroup'] = $firstInSubGroup;

//
// answer
//

?>

<br />
<FORM ACTION="/Quiz/quiz.php" METHOD="post" AUTOCORRECT="off" AUTOCAPITALIZE="off" AUTOCOMPLETE="off" NAME="question">
<?php
printf("%s: <INPUT TYPE='text' NAME='ans' SIZE='40' style='font-weight: bold; font-size:10pt' id='ans'>", $language); ?>
<INPUT TYPE="submit" style='font-weight: bold; font-size:14pt' VALUE="Answer">
</h3>
</FORM>

<SCRIPT TYPE="text/javascript">
document.ans.focus();
</SCRIPT>
<br />
<P STYLE="clear: both">

<?php
if ($language !== "Swahili") { printf("<IMG src=%s><br />", $keyboard); }
if ($language === "French") { printf("&oelig; is &#60;ALT&#62;-0156 on numeric keypad<br />"); }
?>
<br />
<br /> <br /> <br />
</DIV> 


<DIV ID="footer">

<?php
$url = rawurlencode(sprintf('%s', $_SESSION['group']));
printf("<I><A HREF='/Quiz/reset.php?group=%s&language=%s'>Reset %s</a></I><br />\n", $_SESSION['group'], $language, $group);
printf("%s:&nbsp;\n", "Groups");
$db = new PDO($database);
$sql = "SELECT g, n, g1, g2, p FROM vGroups";
$results = $db->query($sql);
$previous = "";
$i = 0;

while ($row = $results->fetch())
{
   $g = $row[0];
   $count = $row[1];
   $g1 = $row[2];
   $g2 = $row[3];
   $i++;
   if (strlen($g1) > 0)
   {
      if ($previous <> $g1)
      { // first verb
         printf("<A HREF='/Quiz/quiz.php?group=%s&language=%s'>" . "<B>%s</B>&nbsp;%s (%s)</A>&nbsp; \n", $g, $language, $g1, $g2, $count);
      }
      else
      { // other verb
         printf("<A HREF='/Quiz/quiz.php?group=%s&language=%s'>" . "%s&nbsp;(%s)</A>&nbsp; \n", $g, $language, $g2, $count);
      }

      $previous = $g1;
   }
   else
   { // not verb
      printf("<A HREF='/Quiz/quiz.php?group=%s&language=%s'>" . "<B>%s</B>&nbsp;</A>(%s)&nbsp; \n", $g, $language, $g, $count);
   }
}

printf("<br /><I>Page last modified: %s</I>", date("M j Y", filemtime("quiz.php")));
?>
</DIV>

</BODY>
</HTML> 

