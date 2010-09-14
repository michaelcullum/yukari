# Failnet todo list

* exceptions should be rewritten
* events should be redone entirely
* functions.php should be cleaned up if at all possible
* document configurations as work progresses
* add invoke() on cron tasks for manually triggering them, instead of relying on manualRunTask()
* write the standard ACL layer, add in authorization levels within it
* look at using Doctrine with Failnet, to replace the plain PDO involved
* deprecate the Core\IRC object
* deprecate the current Core\Log object, replace with something more OOP-ish probably
* update socket to make use of newer event objects
