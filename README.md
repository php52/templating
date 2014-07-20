# templating [![Build Status](https://secure.travis-ci.org/ehough/templating.png)](http://travis-ci.org/ehough/templating)

Fork of [Symfony's Templating component](https://github.com/symfony/Templating) compatible with PHP 5.2+.

### Motivation

[Symfony's Templating component](https://github.com/symfony/Templating) is a fantastic templating library,
but it's only compatible with PHP 5.3+. While 97% of PHP servers run PHP 5.2 or higher,
 **26% of all servers are still running PHP 5.2 or lower** ([source](http://w3techs.com/technologies/details/pl-php/5/all)).
It would be a shame to exempt this library from a quarter of the world's servers just because of a few version incompatibilities.

Once PHP 5.3+ adoption levels near closer to 100%, this library will be retired.

### Differences from [Symfony's Templating component](https://github.com/symfony/Templating)

The primary difference is naming conventions of the Symfony classes.
Instead of the `\Symfony\Component\Templating` namespace (and sub-namespaces), prefix the Symfony class names
with `ehough_templating` and follow the [PEAR naming convention](http://pear.php.net/manual/en/standards.php)

An examples of class naming conversion:

    \Symfony\Component\Templating\EngineInterface      ----->    ehough_templating_EngineInterface
    \Symfony\Component\Templating\Storage\FileStorage  ----->    ehough_templating_storage_FileStorage

### Releases and Versioning

Releases are synchronized with the upstream Symfony repository. e.g. `ehough/templating v2.3.1` has merged the code
from `Symfony/Templating v2.3.1`.