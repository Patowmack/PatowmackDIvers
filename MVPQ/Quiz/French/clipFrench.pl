#
# get Italian verbs from http://www.verbix.com/languages/italian.shtml
# program will create records in quizFrench/quiz.sqlite3
# program will create records in G:\XAMPP\xampplite\apache\htdocs\Quiz\quizFrench.sqlite3 (see $db)
#
# Needs:  j' with no space, qu' from scratch

use Win32::Clipboard;
use DBI;
use Encode;

$text = Win32::Clipboard::GetAs(CF_UNICODETEXT);
$text = Encode::decode("UTF16-LE", $text);

@english =('I', 'you (s)', 'he', 'we', 'you (p)', 'they');
@french =('je', 'tu', 'il', 'nous', 'vous', 'ils');
@lines = split(/\n/, $text);

my $group = $ARGV[0];  # modify to get as many parameters as there are? or 
                       # use "
		       
printf("%s\n\n", $group);
# printf("%s\n", $ARGV[0]);
#
# prepare SQL
#
# my $db = DBI->connect("dbi:SQLite:quiz.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});
my $db = DBI->connect("dbi:SQLite:G:/XAMPP/xampplite/apache/htdocs/Quiz/quizSpanish.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});
#insert
   my $ith = $db->prepare(<<SQL) || die "SQL error:" . DBI->errstr;
   INSERT INTO quiz ("group", xOther, english) VALUES (?, ?, ?)
SQL

#
# clipboard
#
my $i;

if ($ARGV[0] =~ /subj/) { $subj = 1; }

foreach (@lines){
   $line = $_;
   if ($subj) { $english[$i] = "that " . $english[$i]; }
   
   $pronoun = splice(@lines0, 0, $#lines0); # remove first
   # better to look for matches on ";" so compound tenses will work
   printf("%s %s->%s ", $pronoun, join(' ', @lines0), $english[$i]);
   $read = <STDIN>;
   chomp($read);
   if ($i ==0 && substr($lines0[0],0,1) =~ /[aeiou]/) {
	   printf("VOWEL\n");
	   $french[0] = "j'";
   }
   $english = $english[$i] . " " . $read;
   $xOther = $french[$i] . " " . join(' ', @lines0);
   if ($subj) { $xOther = "que " . $xOther; }

   $xOther =~ s/' +/'/;
#  $xOther = join(' ', @lines0);
   printf("%s->%s->\'%s\'\n\n", $group, $english, $xOther);
   $ith->execute($group, $xOther, $english);
   $i++;
}

  
