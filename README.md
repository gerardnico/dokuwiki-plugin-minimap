# dokuwiki-plugin-minimap

## Usage

The [Minimap Dokuwiki plugin](https://www.dokuwiki.org/plugin:minimap) generates a [sitemap](https://www.dokuwiki.org/index_sitemap?do=index) but only for the current [namespace](https://www.dokuwiki.org/namespaces) (ie mini-map)

By adding the <minimap> tag in your page, you will generate a mini-map.

See the mini-map plugin page on Dokuwiki [here](https://www.dokuwiki.org/plugin:minimap)

## Example

See the plugin in action [here](http://gerardnico.com/wiki/dokuwiki/minimap).

## Illustration

![The illustration](https://github.com/gerardnico/dokuwiki-plugin-minimap/blob/master/images/minimap_plugin_illustration.png "MiniMap Illustration")

## Installation

Install the plugin using:

  * the [Plugin Manager](https://www.dokuwiki.org/plugin:plugin)
  * [manually](https://www.dokuwiki.org/plugin:Plugins) with the [download URL](http://github.com/gerardnico/dokuwiki-plugin-minimap/zipball/master), which points to latest version of the plugin.


## Syntax

```xml
<minimap suppress="regular expression pattern">
```

where:

  * the "suppress" option will suppress the "regular expression pattern" part of the page title. It uses the function [preg_replace](http://php.net/manual/en/function.preg-replace.php). Actually in the pattern, letters, digits and the following characters are allowed: space, -, _, |, *, .
The use case is when you add to the title of your page already a namespace.

## Example

```xml
<minimap suppress="Dokuwiki - ">
```

With the following page title:
```
Dokuwiki - Plugin Mini Map
```
the mini-map will show the following title:
```
Plugin Mini Map
```

## Configuration and Settings


  * A button is added in the toolbar, you can choose the shortcut key in the [configuration manager](https://www.dokuwiki.org/plugin:config).



