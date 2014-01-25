PHP_Proxy
=========

This will proxy all domains (and subdomains with the correct wildcard hosts) from a source domain to a target domain. It will automatically fix any cookies and links (hence the subdomains part).

# Requirements
* Apache
* PHP
* cURL (included in most PHP installations)

# Configuration

Create a "config.inc.php" file, and populate it with your own domains and their targets -

```php
$domains = array(
	"mydomain.com" => "thepiratebay.se",
	"myotherdomain.com" => "yts.re" // Also catches static.yts.re
);
```

It will take the first domain it finds, so there is no need to forward multiple subdomains unless they are using separate urls.

# Installation

Create the config file, copy the three files to your web server, enjoy...

- [x] .htaccess
- [x] index.php
- [x] config.inc.php
