# dokuwiki-plugin-minimap

## Usage

The [Minimap Dokuwiki plugin](https://www.dokuwiki.org/plugin:minimap) generates a [sitemap](https://www.dokuwiki.org/index_sitemap?do=index) but only for the current [namespace](https://www.dokuwiki.org/namespaces) (ie mini-map)

By adding the `<minimap>` tag in your page, you will generate a mini-map.

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
<minimap suppress="regular expression pattern" includeDirectory="false" debug="false">
```

where:

  * the `suppress` option will suppress the "regular expression pattern" part of the page title. It uses the function [preg_replace](http://php.net/manual/en/function.preg-replace.php). Actually in the pattern, letters, digits and the following characters are allowed: space, -, _, |, *, .
The use case is when you add to the title of your page already a namespace.
  * the `includeDirectory` permits to include the subdirectories in the list (Default=false)
  * the `debug` parameter prints debug information if set to true below the panel header and in the link title (Default=false)

## Example

```xml
<minimap suppress="Dokuwiki - |The Doku - ">
```

With the following page title,
```
Dokuwiki - Plugin Mini Map
The Doku - Syntax
```
The mini-map will show the following page title:
```
Plugin Mini Map
Syntax
```

## Configuration and Settings

### Toolbar Shortcut
A button ![The Button](https://github.com/gerardnico/dokuwiki-plugin-minimap/blob/master/images/minimap.png "MiniMap Button") is added in the toolbar, you can choose the shortcut key in the [configuration manager](https://www.dokuwiki.org/plugin:config).

### Cache
As this plugin has a lot of chance to be used in a [sidebar](https://www.dokuwiki.org/faq:sidebar), you may want to add a `~~NOCACHE~~` [macro](https://www.dokuwiki.org/wiki:syntax#control_macros)


## Releases History
  * 2017-4-29:
     * The list item are working now with the mathjax plugin. The HTML tag were not encoded
     * The list items have no a left margin of 0. It was overwritten by the standard dokuwiki template
  * 2016-06-04:
     * The `includeDirectory` option was added.
  * 2015-12-28:
     * The styling is now targeted only for the mini-map elements and will not interfere with the admin page. See [Issue 2](https://github.com/gerardnico/dokuwiki-plugin-minimap/issues/2).
     * The handle function of the syntax class does not have any other variable instantiation than the one in the syntax.
  * 2015-10-25:
     * First Release.

