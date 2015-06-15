phpBB 3.1 - Whatsapp notifier
==========================
This is an extension for the phpBB forums software. You need an instalation of phpBB 3.1.x to use this.

DESCRIPTION
-------
This Extension adds a new notifier type. Your users can enter a phone number in there profile and can get notifications on Whatsapp.

INSTALATION
----------
To install this extension download it from here and upload the files in your forum under <b>/ext/tas2580/whatsapp</b>.
After that go to the Admin panel of your forum and navigate in to Customise -> Extension Management -> Extensions. Search
this extension in the list of extensions and click on Enable.

GET THE PASSWORD
----------------
To configure your Whatsapp account that should send the notifications you need a phone number that does not use Whatsapp because you can
use Whatsapp only on one device at the same time. Also you need your Whatsapp password, as a Windows user you can use <a href="https://github.com/mgp25/WART">WART</a> to get it.
As a Linux user you can use yowsup to get it.

To install yowsup type:
<pre>apt-get install python-dateutil
wget https://github.com/tgalal/yowsup/archive/master.zip
unzip master.zip
cd yowsup-master/
nano config.cfg</pre>
After that enter: (Replace 49 with your country code)
<pre>cc=49
phone=49{PHONE NUMBER} (without 0 at the beginning)
id=
password=</pre>
Then save the file and go back to console and type
<code>python yowsup-cli registration -c config.cfg -r sms</code>
Now you should get a SMS with a code, type:
<code>python yowsup-cli registration -c config.cfg -R {YOUR CODE WITHOUT -}</code>

SUPPORT
-------
You can get support for this extension on <a href="https://www.phpbb.com/community/viewtopic.php?f=456&t=2320511">phpbb.com</a>
or in german on <a href="https://www.phpbb.de/community/viewtopic.php?f=149&t=234623">phpbb.de</a>. For more informations look at
<a href="https://tas2580.net/downloads/download-14.html">my Website</a>.

Help to translate
-----------------
If you use the extension in your forum and translated it therefor in your language, it would be nice if you would send me a <a href="https://help.github.com/articles/using-pull-requests/">pull request</a>. Also it may be that existing translations after uptates of the extensions are incomplete. So you can help me if you complete or correct existing language files.

LICENSE
-------
<a href="http://opensource.org/licenses/gpl-2.0.php">GNU General Public License v2</a>
