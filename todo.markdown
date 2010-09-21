# Failnet todo list

* exceptions should be rewritten
* functions.php should be cleaned up if at all possible
* document configurations as work progresses
* add invoke() on cron tasks for manually triggering them, instead of relying on manualRunTask()
* write the standard ACL layer, add in authorization levels within it
* look at using Doctrine with Failnet, to replace the plain PDO involved
* deprecate the current Core\Log object, replace with something more OOP-ish probably
* update socket to make use of newer event objects
* update the cron task base, use a mixture of interface and base class
* alter event base to be more general
* alter IRC-based events to extend Event\IRC\IRCBase
* alter runtime-based events to extend Event\Runtime\RuntimeBase
