window.jeopardy = (function (jeopardy) {

    jeopardy.buzzer_topic = 'com.sc2ctl.jeopardy.buzzer';
    jeopardy.buzzer_status_topic = 'com.sc2ctl.jeopardy.buzzer_status';
    jeopardy.question_display_topic = 'com.sc2ctl.jeopardy.question_display';
    jeopardy.question_dismiss_topic = 'com.sc2ctl.jeopardy.question_dismiss';
    jeopardy.question_answer_topic = 'com.sc2ctl.jeopardy.question_answer';
    jeopardy.contestant_score_topic = 'com.sc2ctl.jeopardy.contestant_score';
    jeopardy.daily_double_bet_topic = "com.sc2ctl.jeopardy.daily_double_bet";
    jeopardy.final_jeopardy_topic = "com.sc2ctl.jeopardy.final_jeopardy";
    jeopardy.final_jeopardy_responses_topic = "com.sc2ctl.jeopardy.final_jeopardy_responses";
    jeopardy.final_jeopardy_answer_topic = "com.sc2ctl.jeopardy.final_jeopardy_answers";
    jeopardy.penalty_amount = 500; // amt in milliseconds that you're penalized for clicking early
    jeopardy.buzzer_active_at = false;
    jeopardy.penalty_until = 0;
    jeopardy.host = 'ws://' + window.location.hostname + '/ws';
    jeopardy.admin_mode = false; // Sets admin mode, which will disable feedback like penalties, buzzbuttons, etc.


    var conn = new ab.Session(jeopardy.host,
        function () {
            conn.subscribe(jeopardy.buzzer_topic, handleBuzzEvent);
            conn.subscribe(jeopardy.buzzer_status_topic, processBuzzerActiveResult);
            conn.subscribe(jeopardy.question_display_topic, handleQuestionDisplay);
            conn.subscribe(jeopardy.question_answer_topic, handleQuestionAnswer);
            conn.subscribe(jeopardy.question_dismiss_topic, handleQuestionDismiss);
            conn.subscribe(jeopardy.contestant_score_topic, handleContestantScore);
            conn.subscribe(jeopardy.daily_double_bet_topic, handleDailyDoubleBet);
            conn.subscribe(jeopardy.final_jeopardy_topic, handleFinalJeopardy);
            conn.subscribe(jeopardy.final_jeopardy_responses_topic, handleFinalJeopardyResponses);
            conn.subscribe(jeopardy.final_jeopardy_answer_topic, handleFinalJeopardyAnswers);

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
    jeopardy.getFinalJeopardyModal = function () {
        console.warn("You need to override the getFinalJeopardyModal");
    };
    jeopardy.getFinalJeopardyBetInput = function () {
        console.warn("You need to override the getFinalJeopardyBetInput");
    };
    jeopardy.getFinalJeopardyAnswerInput = function () {
        console.warn("You need to override the getFinalJeopardyBetInput");
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

    jeopardy.attemptQuestionAnswer = function(category, value, contestant, bet, correct) {
        if (correct == undefined) {
            correct = true;
        }

        var payload = {
            category: category,
            value: value,
            contestant: contestant,
            bet: bet,
            correct: correct
        };

        conn.publish(jeopardy.question_answer_topic, payload, [], []);
    };

    jeopardy.
        attemptQuestionDismiss = function (category, value) {
        if (category == undefined || value == undefined) {
            console.warn("Attempted to dismiss with undefined category or value");
            return;
        }
        var payload = {
            category: category,
            value: value
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

    jeopardy.attemptFinalJeopardyDisplay = function(content) {
        var payload = {
            content: content
        };
        conn.publish(jeopardy.final_jeopardy_topic, payload, [], [])
    };

    jeopardy.attemptFinalJeopardyBet = function(playerName, bet) {
        var payload = {
            contestant: playerName,
            bet: bet,
            type: "bet"
        };

        conn.publish(jeopardy.final_jeopardy_responses_topic, payload, [], []);
    };

    jeopardy.attemptFinalJeopardyAnswer = function(playerName, answer) {
        var payload = {
            contestant: playerName,
            answer: answer,
            type: "answer"
        };
        conn.publish(jeopardy.final_jeopardy_responses_topic, payload, [], []);
    };

    jeopardy.attemptGetFinalJeopardyAnswer = function(playerName) {
        var payload = {
            contestant: playerName
        };

        conn.publish(jeopardy.final_jeopardy_answer_topic, payload, [], []);
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

    function handleFinalJeopardyAnswers(topic, data) {
        data = JSON.parse(data);

        var modal = jeopardy.getFinalJeopardyModal();
        var response = modal.find('.contestant-response');
        response.find('.contestant-name').html(data.contestant);
        response.find('.contestant-answer').html(data.answer);
        response.find('.contestant-wager').html(data.bet).attr('data-bet', data.bet);
        response.show('fast');
    }

    function getActivePlayer() {
        var players = jeopardy.getPlayerElements();

        for (var i in players) {
            if ($(players[i]).data('active-player') == true) {
                return $(players[i]).data('player-name');
            }
        }

        console.error("Could not determine active player");
    }

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

    function handleFinalJeopardy(topic, data) {
        data = JSON.parse(data);
        var modal = jeopardy.getFinalJeopardyModal();

        if (data.hasOwnProperty("category")) {
            modal.find('.final-jeopardy-category').html(data.category);
            modal.show('fast');
            $('#final-jeopardy-next').attr('data-current-step', "clue");
            return;
        }

        if (data.hasOwnProperty("clue")) {
            modal.find('.final-jeopardy-clue').html(data.clue);
            $('#final-jeopardy-next').attr('data-current-step', "answer");

            var answer_input = jeopardy.getFinalJeopardyAnswerInput();
            if (answer_input == undefined) return;
            var bet_input = jeopardy.getFinalJeopardyBetInput();
            if (bet_input == undefined) return;

            bet_input.parent().hide('fast');
            answer_input.parent().show('fast');
            return;
        }

        if (data.hasOwnProperty("answer")) {
            if (!jeopardy.admin_mode) {
                var my_answer_input = jeopardy.getFinalJeopardyAnswerInput();
                if (my_answer_input == undefined) return;
                my_answer_input.parent().hide('fast');
                return;
            }

            modal.find('.responses').show('fast');
            modal.find('.answer .content').html(data.answer);
            modal.find('.answer').show('fast');
            $('#final-jeopardy-next').hide('fast'); //TODO this selector is tightly coupled
        }
    }

    function handleFinalJeopardyResponses(topic, data) {
        data = JSON.parse(data);

        if (data.content == "bet") {
            collectFinalJeopardyBet();
        }

        if (data.content == "answer") {
            collectFinalJeopardyAnswer();
        }
    }

    function collectFinalJeopardyBet() {
        var input = jeopardy.getFinalJeopardyBetInput();
        if (input == undefined) {
            return;
        }

        jeopardy.attemptFinalJeopardyBet(getActivePlayer(), input.val());
    }

    function collectFinalJeopardyAnswer() {
        var input = jeopardy.getFinalJeopardyAnswerInput();
        if (input == undefined) {
            return;
        }

        console.log(input.val());
        jeopardy.attemptFinalJeopardyAnswer(getActivePlayer(), input.val());
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

    function handleQuestionAnswer(topic, data) {
        data = JSON.parse(data);
        console.log(data);

        var players = jeopardy.getPlayerElements();
        for (var i in players) {
            if ($(players[i]).hasClass(data.contestant)) {
                var score = parseInt($(players[i]).find('.score').html());
                $(players[i]).find('.score').html(score + parseInt(data.value));
            }
        }

        if (!data.correct) {
            setBuzzerActive(jeopardy.getStatusIndicatorElement());
        }
    }

    function handleQuestionDismiss(topic, data) {
        data = JSON.parse(data);
        console.log(data);

        blankOutQuestionBox(data.category, data.value);
        var modal = jeopardy.getQuestionDisplayModal();
        clearModalData(modal);
        modal.find('.content').first().find('.clue').first().html("");
        modal.hide('fast');

        modal = jeopardy.getDailyDoubleModal();
        clearModalData(modal);
        modal.find('.content').first().find('.clue').first().html("");
        modal.hide('fast');

        setBuzzerInactive(jeopardy.getStatusIndicatorElement());
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

