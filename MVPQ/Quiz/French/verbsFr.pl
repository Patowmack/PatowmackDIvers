#
# get French verbs from http://www.verbix.com/languages/italian.shtml
# program will create records in quizFrench/quiz.sqlite3
#

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
my $db = DBI->connect("dbi:SQLite:quiz.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});

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
   s/; +//; # fix verbix il; elle verb
   $line = $_;
   if ($subj) { $english[$i] = "that " . $english[$i]; }

   @words = split(/\s+/, $line); # 
   printf("words: @words\n");
   $pronoun = splice(@words, 0, 1); #remove first word
   printf("%d\n", $#words);  # 0 for normal  1 for il; elle
   # better to look for matches on ";" so compound tenses will work
   if ($words[0]=~ /^[aeiou]/) { $french[0] = 'j\''; }
   printf("%s %s->%s ", $french[$i], join(' ', @words), $english[$i]);
   $read = <STDIN>;
   chomp($read);
   $english = $english[$i] . " " . $read;
   $xOther = $french[$i] . " " . join(' ', @words);
#  $xOther = $pronoun . " " . join(' ', @words);
   if ($subj) { $xOther = "que " . $xOther; }
   $xOther =~ s/' +/'/; 
#  $xOther = join(' ', @words);
   printf("%s->%s->\'%s\'\n\n", $group, $english, $xOther);
   $ith->execute($group, $xOther, $english);
   $i++;
}

  
