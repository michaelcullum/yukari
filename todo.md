# Failnet todo list

* environment should include a basic autoloader
* environment should hold all objects
* environment should store basic configuration
* bot should manage the current server interaction, take over from core
* bootstrap should perform initial load, then handoff to environment
* exceptions should be rewritten
* events should be redone entirely
* functions.php should be cleaned up if at all possible
* plugin handling should be replaced by allowing "listeners" to be registered with the bot class, for listener-specified event types
* rebuild how configurations work, build for optional configurations, and provide the structure to have required configurations
* document configurations as work progresses