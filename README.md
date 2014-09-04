# gizmola/Navibridge
#A php Class to make Denso NaviBridge url's
==================

### Examples
```php
namespace test;
include __dir__ . '/' . 'src/gizmola/NaviBridge/Navibridge.php';
use gizmola\NaviBridge\Navibridge;

$link = new Navibridge(array('appName' => 'your_app', 'callURL' => 'http://www.gizmola.com'));
$link->addWaypoint(array('title' => 'Hollywood Bowl', 'll' => '34.103919,-118.371739'));

echo '<a href="'  . $link->getTarget() . '">test</a>';
```