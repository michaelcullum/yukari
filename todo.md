# Failnet todo list

* exceptions should be rewritten
* events should be redone entirely
* functions.php should be cleaned up if at all possible
* plugin handling should be replaced by allowing "listeners" to be registered with a dispatcher object, for listener-specified event types
* document configurations as work progresses
* add invoke() on cron tasks for manually triggering them, instead of relying on manualRunTask()
* update cron to use arrayaccess for accessing cron tasks instead of invoke()
* write the standard ACL layer, add in authorization levels within it
* look at using Doctrine with Failnet, to replace the plain PDO involved
* update cron object
* deprecate the Core\IRC object
* deprecate the current Core\Log object, replace with something more OOP-ish probably
* update socket to make use of newer event objects
* deprecate the Core\Plugin object
* toss in the dispatcher core object
* figure out what the hell we still need the Core\Core object for
