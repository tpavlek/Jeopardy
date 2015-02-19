window.jeopardy = (function (jeopardy) {

    jeopardy.buzzer_topic = 'com.sc2ctl.jeopardy.buzzer';
    jeopardy.buzzer_status_topic = 'com.sc2ctl.jeopardy.buzzer_status';
    jeopardy.question_display_topic = 'com.sc2ctl.jeopardy.question_display';
    jeopardy.question_dismiss_topic = 'com.sc2ctl.jeopardy.question_dismiss';
    jeopardy.penalty_amount = 250; // amt in milliseconds that you're penalized for clicking early
    jeopardy.buzzer_active_at = false;
    jeopardy.current_penalty = 0;
    jeopardy.host = 'ws://localhost:9001';
    jeopardy.admin_mode = false; // Sets admin mode, which will disable feedback like penalties, buzzbuttons, etc.


    var conn = new ab.Session(jeopardy.host,
        function () {
            conn.subscribe(jeopardy.buzzer_topic, handleBuzzEvent);
            conn.subscribe(jeopardy.buzzer_status_topic, processBuzzerActiveResult);
            conn.subscribe(jeopardy.question_display_topic, handleQuestionDisplay);
            conn.subscribe(jeopardy.question_dismiss_topic, handleQuestionDismiss);
        },
        function () {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );

    /* These methods should be overridden */

    jeopardy.getStatusIndicatorElement = function () {
        console.warn("You need to override the getStatusIndicatorElement method!");
    };

    jeopardy.getBuzzerButtonElement = function () {
        console.warn("You need to override the getBuzzerButtonElement method!");
    };

    jeopardy.getPenaltyDisplayElement = function () {
        console.warn("You need to override the getPenaltyDisplayElement method!");
    };

    jeopardy.getJeopardyBoardElement = function () {
        console.warn("You need to override the getJeopardyBoardElement method!");
    };
    jeopardy.getQuestionDisplayModal = function () {
        console.warn("You need to override the getQuestionDisplayModal");
    };
    jeopardy.getPlayerElements = function () {
        console.warn("You need to override the getPlayerElements method!");
    };

    /* Our public API */

    jeopardy.attemptBuzz = function (name) {

        if (jeopardy.buzzer_active_at === false) {
            addToPenalty(jeopardy.penalty_amount);
            return;
        }

        var difference = (Date.now() - jeopardy.buzzer_active_at) + jeopardy.current_penalty;

        disableBuzzButton(jeopardy.getBuzzerButtonElement());
        resetPenalty();

        var buzz = {
            'name': name,
            'difference': difference
        };
        conn.publish(jeopardy.buzzer_topic, buzz, [], []);
    };

    jeopardy.attemptBuzzerStatusChange = function (status) {
        var payload = {
            'active': status
        };
        conn.publish(jeopardy.buzzer_status_topic, payload, [], [])
    };

    jeopardy.attemptNewQuestionDisplay = function (categoryName, value) {
        var payload = {
            category: categoryName,
            value: value
        };

        conn.publish(jeopardy.question_display_topic, payload, [], []);
    };

    jeopardy.attemptQuestionDismiss = function (category, value, winner) {
        var payload = {
            category: category,
            value: value,
            winner: winner
        };

        conn.publish(jeopardy.question_dismiss_topic, payload, [], []);
    };


    /* These are library functions */

    function setBuzzerActive(status_indicator) {
        jeopardy.buzzer_active_at = Date.now();
        status_indicator.removeClass('inactive-buzzer').addClass('active-buzzer');
    }

    function setBuzzerInactive(status_indicator) {
        jeopardy.buzzer_active_at = false;
        status_indicator.removeClass('active-buzzer').addClass('inactive-buzzer');
        resetPenalty();
        enableBuzzButton(jeopardy.getBuzzerButtonElement());
    }

    function clearPlayerBuzzes() {
        var players = jeopardy.getPlayerElements();
        for (var i in players) {
            $(players[i]).removeClass('buzz');
        }
    }

    function addPlayerBuzz(playerName) {
        var players = jeopardy.getPlayerElements();
        for (var i in players) {
            if ($(players[i]).hasClass(playerName)) {
                $(players[i]).addClass('buzz');
            }
        }
    }

    function handleBuzzEvent(topic, data) {
        data = JSON.parse(data);
        console.log(data.contestant);
        addPlayerBuzz(data.contestant);
        // We only want the buzz to show for 3 seconds.
        setTimeout(clearPlayerBuzzes, 3000);
        setBuzzerInactive(jeopardy.getStatusIndicatorElement());
    }

    function handleQuestionDisplay(topic, data) {
        data = JSON.parse(data);
        if (data instanceof Array) {
            populateBoard(data);
            return;
        }

        var modal = jeopardy.getQuestionDisplayModal();
        modal.attr('data-category', data.category);
        modal.attr('data-value', data.value);
        modal.show('fast').find('.content').first().html(data.clue);
    }

    function populateBoard(data) {
        var board = jeopardy.getJeopardyBoardElement();
        var categories = board.find('.category');

        for (var i in data) {
            var category_data = data[i];
            var category_column = categories[i];

            $(category_column).attr('data-category', category_data.name);
            $(category_column).find('.category-name').html(category_data.name);

            var questions_column = $(category_column).find('.question.box');

            var questions_data = category_data.questions;
            for (var j in questions_data) {
                if (questions_data[j].used) {
                    $(questions_column[j]).html("");
                    continue;
                }
                $(questions_column[j]).html(questions_data[j].value);
                $(questions_column[j]).attr('data-value', questions_data[j].value);
                $(questions_column[j]).attr('data-category', category_data.name);
            }


        }
    }

    function handleQuestionDismiss(topic, data) {
        data = JSON.parse(data);
        if (data.has_winner) {
            var players = jeopardy.getPlayerElements();
            for (var i in players) {
                if ($(players[i]).hasClass(data.winner)) {
                    var score = parseInt($(players[i]).find('.score').html());
                    $(players[i]).find('.score').html(score + parseInt(data.value));
                }
            }
        }
        console.log(data);
        blankOutQuestionBox(data.category, data.value);
        var modal = jeopardy.getQuestionDisplayModal();
        modal.attr('data-category', "");
        modal.attr('data-value', "");
        modal.find('.content').first().html("");
        modal.hide('fast');
    }

    function blankOutQuestionBox(categoryName, value) {
        var categories = jeo.getJeopardyBoardElement().find('.category').toArray();

        for (var i in categories) {
            if ($(categories[i]).attr('data-category') == categoryName) {
                var questions = $(categories[i]).find('.question.box').toArray();
                for (var j in questions) {
                    if ($(questions[j]).attr('data-value') == value) {
                        $(questions[j]).html("");
                        $(questions[j]).removeClass('question');
                    }
                }
            }
        }
    }


    function resetPenalty() {
        if (jeopardy.admin_mode) {
            return true;
        }

        jeopardy.current_penalty = 0;

        var penalty_span = jeopardy.getPenaltyDisplayElement();
        if (penalty_span == null) {
            return;
        }
        penalty_span.html(jeopardy.current_penalty);
    }

    function addToPenalty(amt) {
        jeopardy.current_penalty += amt;

        var penalty_span = jeopardy.getPenaltyDisplayElement();
        if (penalty_span == null) {
            return;
        }
        penalty_span.html(jeopardy.current_penalty);
    }

    function processBuzzerActiveResult(topic, data) {
        data = JSON.parse(data);

        if (data.active == true) {
            setBuzzerActive(jeopardy.getStatusIndicatorElement());
        } else {
            setBuzzerInactive(jeopardy.getStatusIndicatorElement());
        }
    }

    function disableBuzzButton(buzz_button) {
        if (jeopardy.admin_mode) {
            return;
        }
        buzz_button.prop('disabled', true);
    }

    function enableBuzzButton(buzz_button) {
        if (jeopardy.admin_mode) {
            return;
        }
        buzz_button.prop('disabled', false);
    }

    return jeopardy;

}(window.jeopardy || {}));

