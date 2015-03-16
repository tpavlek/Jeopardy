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
with any game of Jeopardy recorded on [j-archive.com](http://j-archive.com) (all of them). This software ships with a 
`questions.json` from a Teen Tournament episode of Jeopardy.

Finally, you need to start the websocket server. In the application directory run 

```
php server.php
```
This process will take control of your shell, so if you want to continue to use your shell, background it with

```
php server.php &
```

Now you can point your web-browser to whatever URL you set the client directory to run off of, and you'll be able to play!

### Using other JSON files

You need not use the default `questions.json` naming schema. Simply pass the name of your questions file as an argument
to `server.php` and you can use it. This filename does *not* include the extension and must be within the `game_data/` folder.
For example, if you had a file `game_data/my_board.json` you would run:

```
php server.php my_board &
```

Playing
--------

There are two interfaces that exist when playing, the `/admin` interface and the `/play/{user}` interface. These are logically
named.

> Note: There is no security or authentication applied. It is assumed you will only be playing this with clients that you trust.

### Admin

As the "host", load up the `/admin` interface.

* You can load a question by clicking on it. 
* When finished reading, use the "toggle" button to enable the buzzer. The user that buzzed in will have his display element highlighted in yellow for 3 seconds.
* If the user got the question correct, you can left click on his display element; he will be awarded the points and the question dismissed. 
* If the player gets the question incorrect, you can *right* click on his display element, the points will be deducted and the buzzer re-opened.
* If no one has any more answers for the question, click the dismiss button to dismiss the question. The buzzer will be closed.

> Note: There are no protections to prevent a single user from buzzing in twice. Tell them not to.

* Some questions are daily doubles. When one is encountered, you ask the player the amount they wish to bet (you are able to enter
any amount, but the rules say you can only wager money you already have). Click submit and the question will proceed for the value
the user bet.

All data about used questions and player scores are stored in the `$board` variable of the running `server.php`. If you wish
 to restart the game, kill this process and restart it, all running totals will be wiped.
 
> Note: Saving and returning to games is not currently supported. It is possible to manually edit the questions.json file to contain all state, however.
This will be added in a future version.

### Player

As a "player" go to the root of the web-accessible directory. You will be able to choose your name from the list (this list is
pulled from the questions.json file).

* When the buzzer indicator turns green, you can buzz in. Either by clicking the "Buzz In" button or by pressing the letter "j".
* If you buzz in before the indicator turns green, a 500ms penalty will be applied to your buzz.

That's it! It's up to the host to select and display questions, so just tell "Alex" which category and question you would like.

Implementation Information
----------------------------

### Security

There is currently no security associated with this application, nor is there any intention to add any. This is a fun tool
enabling you to play Jeopardy with friends that you trust. If they're dicks, it's best to kick them in their's.

User "accounts" are simply GET arguments on the `/play` route. The software *does* check if the user passed in the GET argument matches a contestant
in the `questions.json` file, however.

Since the feeds for questions/answers/points are all in unencrypted, unauthorized websockets following the WAMP protocol, 
it would be trivial for a user to subscribe to the answers feed and have it appear in their Javascript console.

And, of course, any user can just open the `/admin` view. Perhaps it would be prudent to only allow one connection to `/admin` at once...

### Buzzer

The buzzer is also unsecured and requires that you trust your clients. When a buzzer is enabled, the client records the system
time when the buzzer became available, then records the system time at which the user buzzed in. If the client tried to buzz
in before the buzzer became available, then it will apply a penalty. The client then sends a message to the server informing
that they buzzed in, and provides the time delta in milliseconds.

On the server, once a first buzz is received for an active buzzer, the server will wait 500 more milliseconds for additional
buzzes, and then compute which one had the shortest buzz-in-time. This mitigates the effect of network latency. If a user
had network ping of greater than 500ms one way to your server, his "winning" buzz could potentially be lost. However, if this
is the case, inform the user that using a potato as a modem is not supported behaviour.

Obviously this means that since the client is sending an authoritative delta of time it took them to buzz, if the client was
malicious, they could win (or at least tie) every time. Don't play with dicks.

Testing
--------
There are some tests written. Not a lot.

```
phpunit
```

Contributing
-------------

If you find a bug, send a pull or file an issue.

If you want to add significant new features or change functionality, file an issue first or fire an email
to troy@depotwarehouse.net, so I can ensure it fits in with the idea for this project :)
