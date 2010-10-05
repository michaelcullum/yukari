# Failnet todo list

* functions.php should be cleaned up if at all possible
* document configurations as work progresses
* write the standard ACL layer, add in authorization levels within it
* look at using Doctrine with Failnet, to replace the plain PDO involved
* deprecate the current Core\Log object, replace with something more OOP-ish probably
* update socket to make use of newer event objects
* write Failnet\Event\IRC\Response
* write Failnet\Event\Runtime\RuntimeBase
* write Failnet\Event\Runtime\(all)
* write Failnet\Language\Package\PackageBase (provide automatic json building support, for constructing the JSON language files that will be held within data/language/)
* write Failnet\Language\Package\PackageInterface
