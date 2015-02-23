Depotwarehouse.net's Jeopardy Player
=====================================

This is a full-featured web application that allows a group of super-best-friends to play [Jeopardy!](http://www.jeopardy.com/) 
games with each other. The application is implemented using [ReactPHP](http://reactphp.org/) and websockets.

Installation
-------------

Simply clone the project onto your webserver and run `composer install`. This will download all the dependencies.

> Note: This project runs its websockets on port 9001. This port will need to be open to the public.

Point your webserver to the `client/` subdirectory, and host from there. The index.php method routes all URLs "prettily",
so you will need to have URL rewriting enabled on your webserver. The application ships with an Apache-compatible .htaccess
file.

You will need to supply a `questions.json` file and put it in `game_data/`. You can generate one of these files using 
our [jeopardy-parser](https://github.com/tpavlek/jeopardy-parser) application. Using this you can quickly get up and running
with any game of Jeopardy recorded on [j-archive.com](http://j-archive.com) (all of them).

Finally, you need to start the websocket server. In the application directory run 

```
php server.php
```
This process will take control of your shell, so if you want to continue to use your shell, background it with

```
php server.php &
```

Now you can point your web-browser to whatever URL you set the client directory to run off of, and you'll be able to play!

Playing
--------
//TODO
