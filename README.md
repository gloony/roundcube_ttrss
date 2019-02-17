# [TTRSS on RoundCube](https://github.com/gloony/roundcube_ttrss)

Embed your [TTRSS](https://tt-rss.org) account into your [RoundCube](https://roundcube.net) (used the [TTRSS API](https://git.tt-rss.org/fox/tt-rss/wiki/ApiReference))

**This plugin work only with RoundCube 1.4+ and with elastic skin**

# Installation

```
You need a fully functional TTRSS server
Install this content into your_roundcube_root/plugins/ttrss/
Configure your config.inc.php to add this plugin
Go to your settings into your RoundCube instance and add your login and server
```

# ToDo

```
Search menu
Not refresh headlines on update a label on articles
Find a way to count all articles by feed (and activate button last page)
Maybe adapt the code for older skins, if there is interest from people (not plannified yet)
Optimization
```

# Limitations

```
Due to API limitation, you : 
> Cannot create, rename or delete label
> Cannot create, rename or delete category
> Cannot move Feed to another category
> Cannot change order id of the feed (for this reason, I choose to sort by alphabetic order)

So for all these actions, you need to log in into your TTRSS instance, and while the API doesn't change, I cannot change that

Because I use localStorage for storing current feeds selection and more, the use of multiple instance of roundcude_ttrss on same browser can be hazardous
But it takes the advantage to let you your session when the browser is closed at the same place than before
```

# Screenshots

![roundcube_ttrss Main](screenshots/Main.png)
![roundcube_ttrss Settings](screenshots/Settings.png)

# OpenSource used

[tt-rss-api-php-class by tofika](https://github.com/tofika/tt-rss-api-php-class)

[favico.js by ejci](https://github.com/ejci/favico.js)

[elastic by roundcube](https://github.com/roundcube/elastic)
