window.jeopardy = (function (jeopardy) {

    jeopardy.buzzer_topic = 'com.sc2ctl.jeopardy.buzzer';
    jeopardy.buzzer_status_topic = 'com.sc2ctl.jeopardy.buzzer_status';
    jeopardy.question_display_topic = 'com.sc2ctl.jeopardy.question_display';
    jeopardy.question_dismiss_topic = 'com.sc2ctl.jeopardy.question_dismiss';
    jeopardy.contestant_score_topic = 'com.sc2ctl.jeopardy.contestant_score';
    jeopardy.daily_double_bet_topic = "com.sc2ctl.jeopardy.daily_double_bet";
    jeopardy.penalty_amount = 500; // amt in milliseconds that you're penalized for clicking early
    jeopardy.buzzer_active_at = false;
    jeopardy.penalty_until = 0;
    jeopardy.host = 'ws://' + window.location.hostname + ':9001';
    jeopardy.admin_mode = false; // Sets admin mode, which will disable feedback like penalties, buzzbuttons, etc.


    var conn = new ab.Session(jeopardy.host,
        function () {
            conn.subscribe(jeopardy.buzzer_topic, handleBuzzEvent);
            conn.subscribe(jeopardy.buzzer_status_topic, processBuzzerActiveResult);
            conn.subscribe(jeopardy.question_display_topic, handleQuestionDisplay);
            conn.subscribe(jeopardy.question_dismiss_topic, handleQuestionDismiss);
            conn.subscribe(jeopardy.contestant_score_topic, handleContestantScore);
            conn.subscribe(jeopardy.daily_double_bet_topic, handleDailyDoubleBet);
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
    jeopardy.getDailyDoubleModal = function () {
        console.warn("You need to override the getDailyDoubleModal");
    };
    jeopardy.getPlayerElements = function () {
        console.warn("You need to override the getPlayerElements method!");
    };

    /* Our public API */

    jeopardy.attemptBuzz = function (name) {

        if (jeopardy.buzzer_active_at === false) {
            enablePenalty();
            return;
        }
        var difference = 0;

        var now = Date.now();
        if (jeopardy.penalty_until > now) {
            difference = (now - jeopardy.buzzer_active_at) + jeopardy.penalty_amount;
        } else {
            difference = (now - jeopardy.buzzer_active_at);
        }

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

    jeopardy.
        attemptQuestionDismiss = function (category, value, winner, bet) {
        if (category == undefined || value == undefined) {
            console.warn("Attempted to award with undefined category or value");
            return;
        }
        var payload = {
            category: category,
            value: value,
            winner: winner,
            bet: bet
        };

        conn.publish(jeopardy.question_dismiss_topic, payload, [], []);
    };

    jeopardy.attemptDailyDoubleBet = function (category, value, bet) {
        var payload = {
            category: category,
            value: value,
            bet: bet
        };

        conn.publish(jeopardy.daily_double_bet_topic, payload, [], []);
    };

    jeopardy.attemptSetPlayerScore = function (playerName, score) {

    };


    /* Helper public functions (which should probably be moved to another module sometime */
    jeopardy.getRealModalValue = function(modal) {
        var bet = modal.attr('data-bet');
        var value = modal.attr('data-value');

        if (bet != undefined && bet != null) {
            return bet;
        }

        return value;
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
        console.log(data);
        addPlayerBuzz(data.contestant);
        // We only want the buzz to show for 3 seconds.
        setTimeout(clearPlayerBuzzes, 3000);
        setBuzzerInactive(jeopardy.getStatusIndicatorElement());
    }

    function handleContestantScore(topic, data) {
        data = JSON.parse(data);
        for (var i in data) {
            updateContestantScore(data[i].name, data[i].score);
        }
    }

    function updateContestantScore(contestant, score) {
        $('.player.' + contestant).find('.score').first().html(score);
    }

    function handleDailyDoubleBet(topic, data) {
        var modal = jeopardy.getDailyDoubleModal();
        clearModalData(modal);
        modal.hide('fast');
        data = JSON.parse(data);

        showQuestionModal(data.category, data.value, data.clue, data.answer, data.bet);
    }

    function handleQuestionDisplay(topic, data) {
        data = JSON.parse(data);
        if (data instanceof Array) {
            populateBoard(data);
            return;
        }

        if (data.daily_double) {
            showDailyDouble(data);
            return;
        }

        data.bet = (data.bet != null && data.bet != undefined) ? data.bet : null;
        showQuestionModal(data.category, data.value, data.clue, data.answer, data.bet);
    }

    function showDailyDouble(data) {
        var modal = jeopardy.getDailyDoubleModal();
        setModalData(modal, data);
        modal.show('fast');
    }

    function showQuestionModal(category, value, clue, answer, bet) {
        var modal = jeopardy.getQuestionDisplayModal();
        setModalData(modal, { category: category, value: value, bet: bet });
        modal.find('.content').first().find('.clue').first().html(clue);
        if (jeopardy.admin_mode) {
            modal.find('.content').first().find('.answer').first().show();
            modal.find('.content').first().find('.answer').first().find('.content').html(answer);
        }
        modal.show('fast');
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
                    console.log("used");
                    clearQuestionBox($(questions_column[j]));
                    continue;
                }
                $(questions_column[j]).find('.clue').first().html(questions_data[j].value);
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
                    $(players[i]).find('.score').html(score + parseInt(data.bet));
                }
            }
        }
        blankOutQuestionBox(data.category, data.value);
        var modal = jeopardy.getQuestionDisplayModal();
        clearModalData(modal);
        modal.find('.content').first().find('.clue').first().html("");
        modal.hide('fast');
    }

    function blankOutQuestionBox(categoryName, value) {
        var categories = jeo.getJeopardyBoardElement().find('.category').toArray();

        for (var i in categories) {
            if ($(categories[i]).attr('data-category') == categoryName) {
                var questions = $(categories[i]).find('.question.box').toArray();
                for (var j in questions) {
                    if ($(questions[j]).attr('data-value') == value) {
                        clearQuestionBox($(questions[j]));
                    }
                }
            }
        }
    }

    function setModalData(modal, data) {
        modal.attr('data-category', data.category);
        modal.attr('data-value', data.value);
        if (data.bet != null) {
            modal.attr('data-bet', data.bet);
        }
    }

    function clearModalData(modal) {
        modal.attr('data-category', null);
        modal.attr('data-value', null);
        modal.attr('data-bet', null);
    }



    function clearQuestionBox(questionBox) {
        questionBox.unbind("click");
        questionBox.html("");
        questionBox.removeClass('question');
    }


    function resetPenalty() {
        if (jeopardy.admin_mode) {
            return true;
        }

        var now = Date.now();
        if (now < jeopardy.penalty_until) {
            return;
        }

        jeopardy.penalty_until = 0;

        var penalty_span = jeopardy.getPenaltyDisplayElement();
        if (penalty_span == null) {
            return;
        }
        penalty_span.html("");
    }

    function enablePenalty() {
        jeopardy.penalty_until = Date.now() + jeopardy.penalty_amount;

        var penalty_span = jeopardy.getPenaltyDisplayElement();
        if (penalty_span == null) {
            return;
        }
        penalty_span.html("Penalty!");

        setTimeout(resetPenalty, 4 * jeopardy.penalty_amount);
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

