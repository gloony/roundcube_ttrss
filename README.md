# TTRSS on RoundCube

Embed your [TTRSS](https://tt-rss.org) account into your [RoundCube](https://roundcube.net) (used the [TTRSS API](https://git.tt-rss.org/fox/tt-rss/wiki/ApiReference))

**This plugin work only with RoundCube 1.4+ and with elastic skin**

# Installation

```
You need a fully functional TTRSS server
Install these content into your_roundcube_root/plugins/ttrss/
Configure your config.inc.php to add this plugin
Go to your settings into your RoundCube instance and add a server
```

# ToDo

```
Let the possibility to subscribe or unsubscribe to feeds
Find a way to count all article by feed (and activate button last page)
Make loading indication for pending action
Multiselection for read, star or tag articles
Make timer for count unread items better (use getCounters and not reload all getTree each time)
More options on settings (auto refresh timer, ...)
Search menu
Sort menu
Optimization
```

# Bugs

```
Localization to improve and some texts was never changed from the Mail template
```

# Limitations

```
Due to API limitation, is not possible to : 
> Cannot create, rename or delete Label
> Cannot create, rename or delete category
> Cannot move Feed to another category
> Cannot change order id of feed (for this reason, I choose to sort by alphabetic order)

So for all these actions, your need to log in into your TTRSS instance and while the API doesn't change, I cannot make an update for that
```

# Screenshots

![roundcube_ttrss Main](screenshots/Main.png)
![roundcube_ttrss Settings](screenshots/Settings.png)

# OpenSource used

[tt-rss-api-php-class by tofika](https://github.com/tofika/tt-rss-api-php-class)

[favico.js by ejci](https://github.com/ejci/favico.js)

[elastic by roundcube](https://github.com/roundcube/elastic)