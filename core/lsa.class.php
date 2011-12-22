<?php

class SiteemoLSA {
  var $url;
  var $prc;
  var $text;
  var $proxy;
  var $timeout = 5;
  var $weights = array();
  var $ranks = 0;
  var $limit = 10;
  var $errornumber;
  var $errormessage;
  var $enablecache = 0;
  var $cachedir;
  var $cachetime = 86400;
  var $keywords;
  var $keyphrases;
  var $timing;
  var $dophrases;

  function SetURL($url){
    if ($url) {
	  $this->url = $url;
 	  $this->prc=1;
	  return true;
	} else {
	  $ErrorNumber = 1;
	  $ErrorMessage = "The URL wwas empty";
	  return false;
	}
  }

function DoPhrases($bool)
{
$this->dophrases = $bool;
}

function SetText($text){
    if ($text) {
	  $this->text = $text;
	  $this->prc=2;
	  return true;
	} else {
	  $ErrorNumber = 9;
	  $ErrorMessage = "The Text was empty";
	  return false;
	}
  }


  function SetProxy($proxy){
    $pattern = "/\d+\.\d+\.\d+\.\d+:\d+/";
    if (preg_match($pattern, $proxy, $match)) {
	  $this->proxy = $proxy;
	  return true;
	} else {
	  $this->ErrorNumber = 2;
	  $this->ErrorMessage = "Proxy should look like 10.11.12.13:8080 or similiar.";
	  return false;
	}
  }

  function SetTimeout($timeout){
    if($timeout > 0){
      $this->timeout = $timeout;
      return true;
    } else {
	  $this->ErrorNumber = 3;
	  $this->ErrorMessage = "timeout should be > 0";
	  return false;
	}
  }
  /*
  function EnableCache($enablecache){
    if(($enablecache > 0) && ($cachedir)){
      $this->enablecache = $enablecache;
      return true;
    } else {
	  $this->ErrorNumber = 4;
	  $this->ErrorMessage = "The cache dir is not set";
	  return false;
	}
  }

  function CacheDir($cachedir){
    if(( $cachedir ) && (is_writable($cachedir))) {
      $this->cachedir = $cachedir;
      return true;
    } else {
	  $this->ErrorNumber = 5;
	  $this->ErrorMessage = "CacheDir empty or is not writable!";
	  return false;
	}
  }

  function CacheTime($cachetime){
    if($cachetime > 0){
      $this->cachetime = $cachetime;
      return true;
    } else {
	  $this->ErrorNumber = 6;
	  $this->ErrorMessage = "CacheTime should be > 0";
	  return false;
	}
  }
*/
  function ReturnRanks($ranks){
      $this->ranks = $ranks;
      return true;
  }

  function ReturnLimit($limit){
    if($limit > 0){
      $this->limit = $limit;
      return true;
    } else {
	  $this->ErrorNumber = 8;
	  $this->ErrorMessage = "limit should be > 0";
	  return false;
	}
  }

  function Process () {
	$start=time();
	set_time_limit(0);
	if ($this->prc == 1) {
		$c=new CURL();
		$content=$c->get($this->url);
	} else {
		$content=$this->text;
	}
	$results=0;
	if (!$results) {
		$content=strtolower($content);
		$content=preg_replace("/</", " <", $content);


//	$content=preg_replace("/<script(.*)>([\w|\W|\d|\D|\?|\.|,|\"|\'|\;|\s|\S]+)<\/script>/Ui", " ", $content);
		$content=preg_replace("/<script(.*)>(.|\n)*<\/script>/Ui", " ", $content);
	    $content=strip_tags($content,'<b><title><i><u><strong><body>');
	    $content=$this->strip_words($content);
		preg_match("/<title>(.*)<\/title>/i", $content, $matches);
		$title=explode(" ", $matches[1]);

		while ($word=array_shift($title)) {
			$this->assign($word,'3');
		}
		$content=preg_replace("/<body(.*)>/U", "<body>", $content);
		$content=preg_replace("/\n|\r/", " ", $content);
		$content=preg_replace("/\s\s+/", " ", $content);
		$t=explode("<body>", $content);
		array_shift($t);
		$body=implode($t);
//	list ($null,$body) = explode("<body>", $content);
		if (!$body) $body = $content;
//    $body=$this->strip($body);
		$body=$this->preparse("b","1.2",$body);
		$body=$this->preparse("strong","1.2",$body);
		$body=$this->preparse("i","1.2",$body);
		$body=$this->preparse("u","1.2",$body);

		$body=$this->strip($body);

		$bo=explode(" ", $body);
		$words_count=count($bo);

		while ($word=array_shift($bo)) {
			$this->assign($word,'1');
		}

		arsort($this->weights);
		$possibles=array_keys($this->weights);
		$kws=array_keys($this->weights);
		if ($this->ranks) {
			$this->keywords=$this->weights;
		} else {
			$this->keywords=array_keys($this->weights);
		}

		if ($this->dophrases == 1) {
			for ($i=0;$i<10;$i++) {
				for ($k=0;$k<10;$k++) {
					if ($i != $k) {
						$sh=$this->rephrase($possibles[$i], $possibles[$k],$body,$words_count);
						$final_phrases=array_merge($final_phrases,$sh);
					}
				}
			}

			for ($i=0;$i<10;$i++) {
				for ($k=0;$k<10;$k++) {
					for ($j=0;$j<10;$j++) {
						if (( $i != $k ) && ($k != $j) && ($i != $j) ) {
							$sh=$this->rephrase3($possibles[$i], $possibles[$k],$possibles[$j],$body,$words_count);
							$final_phrases=array_merge($final_phrases,$sh);
						}
					}
				}
			}
	
	
			foreach ($final_phrases as $phrase=>$freq) {
				if (($final_phrases[$phrase] > 0) and (array_key_exists($this->swap($phrase),$final_phrases))) {$final_phrases[$this->swap($phrase)]=0;}
				if ($this->same($phrase)) $final_phrases[$phrase] = 0;
			}
	
			foreach ($final_phrases as $phrase=>$value) {
				for ($l=0;$l<3;$l++) {
					if (preg_match("/".$kws[$l]."/i", $phrase)) $final_phrases[$phrase]=$final_phrases[$phrase]*4;
				}
			}
			arsort($final_phrases);

			if ($this->ranks) {
				$this->keyphrases=$final_phrases;
			} else {
				$this->keyphrases=array_keys($final_phrases);
			}
		}
		$end=time();
		$this->timing=$end-$start;

	} else {
// cached res
	}
  }

  function preparse ($tag,$weight,$body) {
  	$mask="/<".$tag.">([\w\W\d\D\?\.,\"\'\;\s\S]+)<\/".$tag.">/Ui";
	preg_match_all ($mask, $body,$matches);
	$bolds='';
	while ($match=array_shift ($matches[1])) {
		$bolds.=" $match";
		$body=preg_replace("/<".$tag.">([\w\W\d\D\?\.,\"\'\;\s\S]+)<\/".$tag.">/Ui", "", $body);
	}
	$b=explode(" ", $this->strip($bolds));
	while ($word=array_shift($b)) {$this->assign($word,$weight);}
	return $body;
  }

  function Keywords(){
  array_splice($this->keywords,$this->limit);
    return $this->keywords;
  }

  function Phrases(){
    array_splice($this->keyphrases,$this->limit);

        return $this->keyphrases;

  }

  function Error(){
    if($this->errornumber){
      echo "Error number ".$this->errornumber." : ".$this->errormessage;
      return false;
    } else return true;
  }



function assign ($word,$weight) {
	$posword=preg_replace("/(s)$/", "", $word);
	if (isset($this->weights[$posword]) && $this->weights[$posword]) {
		$word=$posword;
	}
	$posword2=$word."s";
	if (isset($this->weights[$posword2]) && $this->weights[$posword2]) {
		$this->weights[$word]=$this->weights[$posword2];
		$this->weights[$posword2]="";
	}
	if(!isset($this->weights[$word])) $this->weights[$word]=0;
	$this->weights[$word]=$this->weights[$word]+$weight;
}

function rephrase3 ($word1, $word2, $word3, $body,$words_count) {
	$keyphrases=array();
	preg_match_all("/".$word1."([\w\W\d\D\?\.,\"\'\;\s\S]+)".$word2."([\w\W\d\D\?\.,\"\'\;\s\S]+)".$word3."/U", $body, $matches);
	//echo "$word1 - $word2 - $word3<br>";
	$distance_count=0;
	$max_distance_count=0;
	$distances=0;
	foreach ($matches[0] as $id=>$kw) {
	
		if ((strlen($matches[1][$id]) < 3) && (strlen($matches[2][$id]) < 3)) {
			//$keyphrases[]=array(phrase=> $this->strip($kw), avg_distance=> $distance, weight=> 200/$words_count, occurences=> 1);
			
			$distances=$distances+strlen($matches[1][$id].$matches[2][$id]);
			$distance_count++;
			if ($distance_count > $max_distance_count) $max_distance_count = $distance_count;
		
		}
	}
	if ($distance_count>0) {
		$avg_distance=$distances/$distance_count;
		$kw_weight=$distance_count/$words_count;
		$keyphrases[]=array('phrase'=> $this->strip($word1." ".$word2." ".$word3), 'avg_distance'=> $avg_distance, 'weight'=> $kw_weight, 'occurences'=> $distance_count);
	}
	
	//print_r($keyphrases);
	$phrases=array();
	foreach ($keyphrases as $keyphrase) {
		if ($keyphrase['occurences']  > $max_distance_count/2) $phrases[$keyphrase['phrase']]=$keyphrase['weight']*3;
	}
	return $phrases;

}


function rephrase ($word1, $word2,$body,$words_count) {
	$keyphrases=array();
	preg_match_all("/".$word1."([\w\W\d\D\?\.,\"\'\;\s\S]+)".$word2."/U", $body, $matches);
	$distances=0;
	$distance_count=0;
	while ($distance = array_shift($matches[1])) {
		if (strlen($distance) < 3) {
			//$keyphrases[]=array(phrase=> $this->strip($word1.$distance.$word2), avg_distance=> $distance, weight=> 2/$words_count, occurences=> 1);
			
			$distances=$distances+strlen($distance);
			$distance_count++;
			if ($distance_count > $max_distance_count) $max_distance_count = $distance_count;
		
		}
	}
	if ($distance_count>0) {
		$avg_distance=$distances/$distance_count;
		$kw_weight=$distance_count/$words_count;
		$keyphrases[]=array('phrase'=> $this->strip($word1." ".$word2), 'avg_distance'=> $avg_distance, 'weight'=> $kw_weight, 'occurences'=> $distance_count);
	}
	
	//print_r($keyphrases);
	$phrases=array();
	foreach ($keyphrases as $keyphrase) {
		if ($keyphrase['occurences']  > $max_distance_count/2) $phrases[$keyphrase['phrase']]=$keyphrase['weight'];
	}
	return $phrases;
}



function swap ($string) {
list ($f,$t) = explode (" ",$string);
return $t." ".$f;
}
function same ($phrase) {
list($f,$t) = explode(" ", $phrase);
if (preg_replace("/s$/", "", $f) == preg_replace("/s$/", "", $t)) return true;
}
function strip ($page) {
$page= preg_replace('/\s\w\s/', ' ', $page);
$page= preg_replace('/\W/', ' ', $page);
$page= preg_replace('/\d/', ' ', $page);
$page= preg_replace('/\s\s+/', ' ', $page);
$page=preg_replace("/^\s|\s$/", "", $page);
//$page=$this->strip_words($page);
return $page;
}
function unique ($text) {
$t=explode(" ", $text);
return implode(" ", array_unique($t));
}

function strip_words ($page) {
$page=preg_replace("/&(.*);/U", " ", $page);

$words_s="nbsp
pm
just
x
and
why
\.com
\.net
at
we
is
are
as
how
where
who
the
to
for
you
your
site
new
add
this
aboard
absent
according
across
after
against
ahead
of
all
over
along
alongside
amid
among
around
aside
astride
away
from
barring
because
before
behind
below
beneath
beside
besides
between
beyond
but
by
circa
close
concerning
considering
despite
down
due
during
except
excepting
excluding
failing
for
from
in
front
including
inside
instead
into
less
like
minus
near
next
notwithstanding
off
on
top
onto
opposite
out
outside
over
past
pending
per
plus
regarding
respecting
round
save
saving
similar
since
than
through
throughout
till
to
toward
towards
under
underneath
unlike
until
unto
up
upon
versus
via
wanting
while
with
within
without
www
http
find
or
no
our
add
it
more
do
nbsp
can
now
one
get
right
what
not
be
so
pr
use
have
that
only
way
any
quot
a
a\'s
able
about
above
according
accordingly
across
actually
after
afterwards
again
against
ain\'t
all
allow
allows
almost
alone
along
already
also
although
always
am
among
amongst
an
and
another
any
anybody
anyhow
anyone
anything
anyway
anyways
anywhere
apart
appear
appreciate
appropriate
are
aren\'t
around
as
aside
ask
asking
associated
at
available
away
awfully
b
be
became
because
become
becomes
becoming
been
before
beforehand
behind
being
believe
below
beside
besides
best
better
between
beyond
both
brief
but
by
c
c\'mon
c\'s
came
can
can\'t
cannot
cant
cause
causes
certain
certainly
changes
clearly
co
com
come
comes
concerning
consequently
consider
considering
contain
containing
contains
corresponding
could
couldn\'t
course
currently
d
definitely
described
despite
did
didn\'t
different
do
does
doesn\'t
doing
don\'t
done
down
downwards
during
e
each
edu
eg
eight
either
else
elsewhere
enough
entirely
especially
et
etc
even
ever
every
everybody
everyone
everything
everywhere
ex
exactly
example
except
f
far
few
fifth
first
five
followed
following
follows
for
former
formerly
forth
four
from
further
furthermore
g
get
gets
getting
given
gives
go
goes
going
gone
got
gotten
greetings
h
had
hadn\'t
happens
hardly
has
hasn\'t
have
haven\'t
having
he
he\'s
hello
help
hence
her
here
here\'s
hereafter
hereby
herein
hereupon
hers
herself
hi
him
himself
his
hither
hopefully
how
howbeit
however
i
i\'d
i\'ll
i\'m
i\'ve
ie
if
ignored
immediate
in
inasmuch
inc
indeed
indicate
indicated
indicates
inner
insofar
instead
into
inward
is
isn\'t
it
it\'d
it\'ll
it\'s
its
itself
j
k
keep
keeps
kept
know
knows
known
l
last
lately
later
latter
latterly
least
less
lest
let
let\'s
like
liked
likely
little
look
looking
looks
ltd
m
mainly
many
may
maybe
me
mean
meanwhile
merely
might
more
moreover
most
mostly
much
must
my
myself
n
name
namely
nd
near
nearly
necessary
need
needs
neither
never
nevertheless
new
next
nine
no
nobody
non
none
noone
nor
normally
not
nothing
novel
now
nowhere
o
obviously
of
off
often
oh
ok
okay
old
on
once
one
ones
only
onto
or
other
others
otherwise
ought
our
ours
ourselves
out
outside
over
overall
own
p
particular
particularly
per
perhaps
placed
please
plus
possible
presumably
probably
provides
q
que
quite
qv
r
rather
rd
re
really
reasonably
regarding
regardless
regards
relatively
respectively
right
s
said
same
saw
say
saying
says
second
secondly
see
seeing
seem
seemed
seeming
seems
seen
self
selves
sensible
sent
serious
seriously
seven
several
shall
she
should
shouldn\'t
since
six
so
some
somebody
somehow
someone
something
sometime
sometimes
somewhat
somewhere
soon
sorry
specified
specify
specifying
still
sub
such
sup
sure
t
t\'s
take
taken
tell
tends
th
than
thank
thanks
thanx
that
that\'s
thats
the
their
theirs
them
themselves
then
thence
there
there\'s
thereafter
thereby
therefore
therein
theres
thereupon
these
they
they\'d
they\'ll
they\'re
they\'ve
think
third
this
thorough
thoroughly
those
though
three
through
throughout
thru
thus
to
together
too
took
toward
towards
tried
tries
truly
try
trying
twice
two
u
un
under
unfortunately
unless
unlikely
until
unto
up
upon
us
use
used
useful
uses
using
usually
v
value
various
very
via
viz
vs
w
want
wants
was
wasn\'t
way
we
we\'d
we\'ll
we\'re
we\'ve
welcome
well
went
were
weren\'t
what
what\'s
whatever
when
whence
whenever
where
where\'s
whereafter
whereas
whereby
wherein
whereupon
wherever
whether
which
while
whither
who
who\'s
whoever
whole
whom
whose
why
will
willing
wish
with
within
without
won\'t
wonder
would
wouldn\'t
x
y
yes
yet
you
you\'d
you\'ll
you\'re
you\'ve
your
yours
yourself
yourselves
z
zero
are
sun
sep
inch
ll
read
amp
be
all
ve
central
free
offer
title
version
the
lj
ed
web
page
pages
cache
cached
et
al";
$words=explode("\n", $words_s);
$page=preg_replace("/\.|\,|\!|\?|\-/", " ", $page);

while ($word=array_shift($words)) {
$page= preg_replace('/(\s|^|\W|\d)'.trim($word).'(\s|\W|\d|$)/Ui', ' ', $page);
}
$page=preg_replace("/\s\s+/", " ", $page);
return $page;
}
function checkcache ($hash) {

}

function fetchcache ($hash) {

}

}


?>