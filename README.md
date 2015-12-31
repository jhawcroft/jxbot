JxBot 0.91
==========

JxBot is a simple web-based conversational agent - a chat robot.  It is currently [AIML-based](http://www.alicebot.org/aiml.html) and built atop PHP and MySQL.

JxBot can handle moderately complex conversation with users in a natural language, such as English.  The software forms the platform and infrastructure upon which your unique domain/problem specific bot personality runs.

You can find out more about JxBot at the homepage, <http://joshhawcroft.org/jxbot/>.


Host System Requirements
------------------------

It is recommended you use the latest version of PHP available on your server/host.  JxBot has currently been tested with PHP 5.5.

This software requires the following setup:

* Apache
* PHP 5
* PHP PDO + MySQL PDO drivers
* MySQL



Installation
------------

The bot software has a simple, guided installation, however, there are a few things that cannot be done by the installer.

You must create a MySQL database to store the JxBot configuration, options, bot data and logs.  How you do this depends on your hosting situation.  There are various tools you can use, including:

* cPanel
* phpMyAdmin
* the MySQL command-line client

Instructions for cPanel will be made available on the JxBot website.


=== Manual Database Creation with SQL ===

If you have and know how to login to your server with the MySQL command-line client, this is an easy option:

1. Create the database itself:

    CREATE DATABASE jxbot;

2. Create a user for the database (replace 'password' with a suitably long and secure password):

    CREATE USER 'jxbot'@'localhost' IDENTIFIED BY 'password';

3. Grant the user permission to use the newly created database:

    GRANT ALL PRIVILEGES ON jxbot.* TO 'jxbot'@'localhost';

4. Flush MySQL privileges to ensure the new user can actually login.

    FLUSH PRIVILEGES;


=== Downloading and Installing ===

1. If you have not already done so, download a the latest sources from GitHub - <https://github.com/jhawcroft/jxbot> - choose the "Download Zip" option.

2. Extract the zip and upload the contents to your web server.
(you can leave out the wordpress-plugin folder and docs folder)

3. Use your favorite web browser to navigate to the bot directory on your web host, for example:

    http://www.your-site-name.com/bot/

4. You should now see the installation page.  If you do not, make sure you have pointed your web browser at the directory of the bot files on your web host.

5. Fill the form with the details of the database you created previously.  Click 'Continue' when you're done and sure the details provided are correct. Using our example:

    Host: localhost   (you can usually leave this as-is unless you know your database server has a specific address)
    Database Name: jxbot
    Table Prefix:  (ignore this - this is not yet supported!)
    Username: jxbot
    Password: password
    
6. You will be asked to provide a handful of essential, basic configuration details:

  * a name for your bot
  * an administration account password
  * the timezone local to you or your bot

Don't worry if you're not sure about these, you can change them all later.  *Be sure to memorise or write down your administration password though - if you loose it, you may loose access to your bot*.

7. Click 'Install'.  If everything goes smoothly, you will see a message indicating that Installation was Successful.

Unfortunately, at present if anything goes wrong with the installation, including entering incorrect database credentials, you will need to delete the config.php file in the jxbot directory on your server, and restart the web-based installation process.  This situation should be improved by the time of the version 1.0 release.

8. Follow the link to go to the login page for your bot.  The username is 'admin' (without the quotes) and the password is the administration password you configured in step 6.

Congratulations!  Initial installation is complete.  You should now see the Dashboard screen of your newly installed bot system.



Bot Configuration and Setup
---------------------------

Out of the box, your bot doesn't know anything.  You will need to obtain a freely available AIML set, or develop your own, to load your bot's database with knowledge.

There are many AIML sets available, most prominently those listed on the Alicebot/AIML homepage <http://www.alicebot.org/downloads/sets.html>.

A somewhat 'standard' US-English set is the A.L.I.C.E. AIML set:
<https://code.google.com/p/aiml-en-us-foundation-alice/>.

To load your AIML set, you will need to upload it to the jxbot/aiml directory on your server, either using your favorite file transfer software or the File Upload facility in the Import / Export section of the administration area.

In the meanwhile, there are a variety of system and personality options that can be configured:

* Bot:  the Bot administration page includes general settings, such as your Bot's name and birthday, as well as a bunch of pre-loaded predicates that you will most probably want to change from their defaults!

* System:  the System administration page includes a place to change your administration password, access to various security and language settings and a switch to put your bot 'Online' - allowing public access to the bot from the Internet.

* Import/Export:  this administration page provides the ability to:

  * upload & delete individual AIML files
  * load all AIML files
  * load individual AIML files
  * unload all files

There is also a Log so you can monitor the AIML import process.

* Chat:  the Chat administration page provides administrator chat access to the bot to test your AIML before it goes online.  It also provides access to the public chat logs, and the ability to configure default values for client predicates should you need it.

* Dashboard:  last but not least, the Dashboard is the place where you enter the administration area upon login.  It provides a large array of real-time statistics on the size of your bot database, the load and performance of your bot system, and longer-term averages.

More detailed information on each of these areas and the other facilities in JxBot can be found on the website <joshhawcroft.org/jxbot/>.



Known Issues
------------

=== 0.91 ===
* Unloading individual AIML files doesn't work, you must unload all files at present
* the Database facility lookup and training functionality could do with improvement
* there is currently no way to export AIML from the database
* the Installation doesn't handle problems at all really



Feedback and Support
--------------------

This is an open-source product, developed (at present) by one man, in my spare time.  Whilst I will endeavour to answer queries and assist where I can, you are encouraged to familiarise yourself with all the available online resources - both on the [JxBot website](joshhawcroft.org/jxbot/) and elsewhere - as well as the online communities that have been established around AIML.

Send your feedback and enquiries to dev at joshhawcroft dot org.

Your feedback is most welcome and encouraged.



Contributing
------------

If you would like to help with the development of JxBot, or provide assistance with Documentation or Support, feel free to get in touch.  Whilst I will gladly accept skilled assistance, I have high standards for the engineering which I myself have not yet met, so be warned!  I do not bite either, however, so please don't be shy.  :-)

Josh
30 December 2015.



Frequently Asked Questions
--------------------------

=== What AIML features and versions does it support? ===

JxBot is currently AIML 1.0 compliant with minor exceptions, and supports most of the draft AIML 2.0 standard with some caveats.  Complete version 2.0 support is intended in future, so long as individual features do not compromise the security, performance or robustness of this implementation.

AIML 1.0 support is complete with these exceptions:

* no learn, system or gossip support (learn is replaced with a better function in AIML 2, system is a security risk that needs careful implementation and gossip was removed in AIML 2 as it isn't particularly useful).

JxBot supports most of the draft AIML 2.0 specification (as at December 2015), including:

* the high-priority word operator: $
* the new zero+ wildcards, ^ and #
* sets
* maps
* request and response tags
* unbound predicate checks
* tag/attribute syntax
* explode
* normalize and denormalize
* program and vocabulary tags

It does not yet support:
* date formats
* date and time intervals
* sraix or system
* learn and learnf


=== What is the philosophy of JxBot? ===

The philosophy of JxBot is in keeping with [my principles for software in general](http://joshhawcroft.org/2015/11/essential-criteria-for-good-software/); the user interface shall be minimalist and clean, and the engineering robust, clean and elegant.  The feature-set will be adequate but not bloated.  After 1.0 release, releases will be infrequent, except when security is impacted.

If the project becomes bloated I will willingly sacrifice functionality for clarity.


=== How do I pronounce the name? ===

It’s up to you – the two most common pronunciations are:

* JAY-EX-BOT
* JEX-BOT


=== Does it support non-English languages? ===

In theory, it should.  Some attempt has been made to properly support the UTF-8 text encoding and handle non-latin characters, including Japanese.  I would welcome feedback on this.


=== Is there a Wordpress plugin? ===

Yes, there is a very basic wordpress plugin bundled in the main jxbot directory.


=== Does it support multiple bots? ===

Not yet.  If this is something you feel you would like, feel free to write and let me know.  It's certainly on the cards if people want it.


=== What access methods does it support? ===

You can use the PHP API or the simple-AJAX API.

Alternatively, you can simply customise the included example HTML file.


=== Have you heard of Program O, or Program E? ===

Yes.  Program E doesn't appear to have been updated or supported since 2005.  On inspection I found Program O to be a little bit too much like spaghetti behind the cute interface.  Obviously substantial effort has gone into the program, however, the engineering just wasn't going to end up being what I had in mind without a complete rewrite.  Although the engineering in JxBot has substantial room for improvement at this early stage, I have a very clear philosophy in mind.










