// NOTE: buzzer.js *must* be included before this file.

window.jeopardy = (function (jeopardy, buzzer, question) {

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

    jeopardy.host = 'wss://' + window.location.hostname + '/ws';
    jeopardy.buzz_display_time = 4500;
    jeopardy.admin_mode = false; // Sets admin mode, which will disable feedback like penalties, buzzbuttons, etc.


    var final_jeopardy_response = null;


    var conn = new ab.Session(jeopardy.host,
        function () {
            conn.subscribe(jeopardy.buzzer_topic, handleBuzzEvent);
            conn.subscribe(jeopardy.buzzer_status_topic, handleBuzzerStatusEvent);
            conn.subscribe(jeopardy.question_display_topic, handleQuestionDisplay);
            conn.subscribe(jeopardy.question_dismiss_topic, handleQuestionDismiss);
            conn.subscribe(jeopardy.question_answer_topic, handleQuestionAnswer);
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

    jeopardy.getStatusIndicatorElement = function () { console.warn("You need to override the getStatusIndicatorElement method!"); };
    jeopardy.getBuzzerButtonElement = function () { console.warn("You need to override the getBuzzerButtonElement method!"); };
    jeopardy.getPenaltyDisplayElement = function () { console.warn("You need to override the getPenaltyDisplayElement method!"); };
    jeopardy.getJeopardyBoardElement = function () { console.warn("You need to override the getJeopardyBoardElement method!"); };
    jeopardy.getQuestionDisplayModal = function () { console.warn("You need to override the getQuestionDisplayModal"); };
    jeopardy.getDailyDoubleModal = function() { console.warn("You need to override the getDailyDoubleModal"); };
    jeopardy.getFinalJeopardyModal = function () { console.warn("You need to override the getFinalJeopardyModal"); };
    jeopardy.getFinalJeopardyBetInput = function () { console.warn("You need to override the getFinalJeopardyBetInput"); };
    jeopardy.getFinalJeopardyAnswerInput = function () { console.warn("You need to override the getFinalJeopardyBetInput"); };
    jeopardy.getPlayerElements = function () { console.warn("You need to override the getPlayerElements method!"); };

    /* Our public API */

    jeopardy.attemptBuzz = function (name) {

        var difference = buzzer.buzz();
        if (difference === false) {
            // buzzer is not active, assign a penalty.
            showPenalty();
            return;
        }
        if (difference === true) {
            // The user has already buzzed.
            console.log("User has already buzzed in! Keep on hammerin' dat j key...");
            return;
        }

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

    jeopardy.attemptQuestionDismiss = function ()
    {
        if (!question.hasQuestion()) {
            console.warn("Attempted to dismiss an undefined question");
        }

        var payload = {
            category: question.getCategory(),
            value: question.getQuestionValue()
        };

        conn.publish(jeopardy.question_dismiss_topic, payload, [], []);
    };

    jeopardy.attemptQuestionAnswer = function(contestant, correct) {
        // The default behaviour is to assume that the answer was correct, unless otherwise specified.
        if (correct == null) {
            correct = true;
        }

        var payload = {
            category: question.getCategory(),
            value: question.getQuestionValue(),
            contestant: contestant,
            bet: question.getBetValue(),
            correct: correct
        };

        conn.publish(jeopardy.question_answer_topic, payload, [], []);
    };



    jeopardy.attemptDailyDoubleBet = function (bet) {
        var payload = {
            category: question.getCategory(),
            value: question.getQuestionValue(),
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

    jeopardy.attemptAwardFinalJeopardyAmount = function(playerName, correct) {

        if (final_jeopardy_response == null) {
            return;
        }

        if (final_jeopardy_response.contestant != playerName) {
            return;
        }

        jeopardy.attemptChangePlayerScore(playerName, final_jeopardy_response.bet * ((correct) ? 1 : -1));
    };

    jeopardy.attemptChangePlayerScore = function (playerName, score) {
        var payload = {
            contestant: playerName,
            diff: score
        };

        conn.publish(jeopardy.contestant_score_topic, payload, [], [])
    };

    /* These are library functions */

    /**
     * This function is called whenever the system receives that a player has successfully buzzed in.
     *
     * We should show which player has buzzed in, and then deactivate the buzzer.
     * @param topic
     * @param data
     */
    function handleBuzzEvent(topic, data) {
        data = JSON.parse(data);
        addPlayerBuzz(data.contestant);
        // We only want the buzz to show for 3 seconds.
        buzzer.deactivate(jeopardy.getStatusIndicatorElement());

    }

    /**
     * This function is called whenever the system recieves a change in the buzzer status.
     *
     * @param topic
     * @param data
     */
    function handleBuzzerStatusEvent(topic, data) {
        data = JSON.parse(data);

        if (data.active == true) {
            buzzer.activate(jeopardy.getStatusIndicatorElement());
        } else {
            buzzer.deactivate(jeopardy.getStatusIndicatorElement());
        }
    }

    /**
     * This function is called whenever we recieve any information in the question display topic.
     *
     * If we get an array, we will populate the board with it. If this is an individual question, we will display it
     * in the modal or collect the daily double bet, depending on the type of question we received.
     *
     * @param topic
     * @param data
     */
    function handleQuestionDisplay(topic, data)
    {
        data = JSON.parse(data);
        // If we have recieved an array back, we're just starting up and want to populate the board with questions.
        if (data instanceof Array) {
            populateBoard(data);
            return;
        }

        question.setQuestion(data);

        if (question.isDailyDouble()) {
            showDailyDouble(jeopardy.getDailyDoubleModal());
            return;
        }

        showQuestion(jeopardy.getQuestionDisplayModal());
    }

    /**
     * This function is called whenever we receive data in the question dismissal topic.
     *
     * We need to remove its entry from the list of available clues, and we need to clear data from the question modal.
     * Since we know that we have yet to select another question, we will automatically disable the buzzer if it was
     * still enabled.
     *
     * @param topic
     * @param data
     */
    function handleQuestionDismiss(topic, data) {
        data = JSON.parse(data);

        blankOutQuestionBox(data.category, data.value);
        hideQuestion(jeopardy.getQuestionDisplayModal());

        question.clear();
        buzzer.deactivate(jeopardy.getStatusIndicatorElement());
    }

    /**
     * This function is called whenever we receive a message that a player has answered a question.
     *
     * Note that this will simply update the players' score based on their answer, and if it was incorrect then we
     * will activate the buzzer again for another round of answers. If the answer was correct, this event will be received,
     * and then a separate question dismissal event will also come along.
     *
     * @param topic
     * @param data
     */
    function handleQuestionAnswer(topic, data) {
        data = JSON.parse(data);

        updateContestantScore(data.contestant, data.value, true);

        if (!data.correct) {
            buzzer.activate(jeopardy.getStatusIndicatorElement());
        }
    }


    /**
     * Called whenever we receive an update to the contestant score topic.
     *
     * This will be either when the game starts and we're getting caught up with the current data or if there is an arbitrary
     * update to a contestant's score sent out by an admin.
     *
     * @param topic
     * @param data
     */
    function handleContestantScore(topic, data) {
        data = JSON.parse(data);

        if (data instanceof Array) {
            for (var i in data) {
                updateContestantScore(data[i].name, data[i].score);
            }
            return;
        }

        updateContestantScore(data.name, data.score);
    }

    /**
     * Called whenever we have received a bet on a daily double. We'll set it in the question state, and transition
     * to showing the regular clue for the question. The flow after this will be no different from a regular question.
     *
     * @param topic
     * @param data
     */
    function handleDailyDoubleBet(topic, data) {
        data = JSON.parse(data);

        question.setBet(data.bet);

        hideDailyDouble(jeopardy.getDailyDoubleModal());
        showQuestion(jeopardy.getQuestionDisplayModal());
    }

    /**
     * This is the main progressor through final jeopardy.
     *
     * The server expects certain "current steps" to be sent to it, and it will respond differently based on the current
     * step.
     *
     * 1. The server will send a category. To get to the next step we need to send it a payload to this topic with
     *     { content: 'clue' }
     * 2. The server will send a clue. To get to the next step we need to send it a payload to this topic with
     *     { content: 'answer' }
     * 3. The server will send an answer. At that point we want to hide the next button, and display buttons with each
     *     of the contestant's names. Clicking on each of those will display the contestant's response.
     *
     * @param topic
     * @param data
     */
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

    /**
     * This is received when the server is sending a collection request for final jeopardy responses.
     *
     * There are two types of collection requests:
     * 1. { content: 'bet' } in which each contestant should send in the amount that they bet on final jeopardy
     * 2. { content: 'answer' } in which each contestant should send in their response to final jeopardy.
     *
     * This requires no interaction and the bets and answers will be automatically collected and sent in.
     * @param topic
     * @param data
     */
    function handleFinalJeopardyResponses(topic, data) {
        data = JSON.parse(data);

        if (data.content == "bet") {
            collectFinalJeopardyBet();
        }

        if (data.content == "answer") {
            collectFinalJeopardyAnswer();
        }
    }

    /**
     * This is received when we have received information about a contestant's final jeopardy answer. We will have
     * explicitly requested this as an admin (unless something has gone wrong) and we should display it for all clients.
     *
     * @param topic
     * @param data
     */
    function handleFinalJeopardyAnswers(topic, data) {
        data = JSON.parse(data);

        var modal = jeopardy.getFinalJeopardyModal();

        final_jeopardy_response = data;
        var response = modal.find('.contestant-response');
        response.find('.contestant-name').html(data.contestant);
        response.find('.contestant-answer').html(data.answer);
        response.find('.contestant-wager').html(data.bet).attr('data-bet', data.bet);
        response.show('fast');
    }

    /* These are some helper functions for final jeopardy */

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

    function getActivePlayer() {
        var players = jeopardy.getPlayerElements();

        for (var i in players) {
            if ($(players[i]).data('active-player') == true) {
                return $(players[i]).data('player-name');
            }
        }

        console.error("Could not determine active player");
    }


    /* These functions handle various display logic concerns */

    /**
     * Displays the buzz notification over a particular player.
     *
     * Will automatically clear this buzz notification after the time configured in jeopardy.buzz_display_time.
     *
     * @param playerName
     */
    function addPlayerBuzz(playerName)
    {
        var clearPlayerBuzzes = function()
        {
            var players = jeopardy.getPlayerElements();
            for (var i in players) {
                $(players[i]).removeClass('buzz');
            }
        };

        var players = jeopardy.getPlayerElements();
        for (var i in players) {
            if ($(players[i]).hasClass(playerName)) {
                $(players[i]).addClass('buzz');

                setTimeout(clearPlayerBuzzes, jeopardy.buzz_display_time)
            }
        }
    }

    function showPenalty()
    {
        var penalty_span = jeopardy.getPenaltyDisplayElement();
        if (penalty_span == null) { return; }

        penalty_span.html("Penalty!");
        // This is a magic number of 3 seconds, after which we will hide the penalty display, if the penalty is expired by then.
        setTimeout(resetPenaltyDisplay, 3000);
    }

    function resetPenaltyDisplay()
    {
        if (buzzer.hasActivePenalty()) { return; }

        var penalty_span = jeopardy.getPenaltyDisplayElement();
        if (penalty_span == null) { return; }
        penalty_span.html("");
    }

    /**
     * Fills in a blank board with the categories and available clues.
     *
     * @param data
     */
    function populateBoard(data) {
        var board = jeopardy.getJeopardyBoardElement();
        var categories = board.find('.category');

        for (var i in data) {
            var category_data = data[i];
            var category_column = categories[i];

            $(category_column).attr('data-category', category_data.name);
            var category_box = $(category_column).find('.category-name');
            category_box.html("<span>" + category_data.name + "</span>")

            var questions_column = $(category_column).find('.question.box');

            var questions_data = category_data.questions;
            for (var j in questions_data) {
                if (questions_data[j].used) {
                    clearQuestionBox($(questions_column[j]));
                    continue;
                }
                var clue_box = $(questions_column[j]).find('.clue').first();
                clue_box.html("<span>" + questions_data[j].value + "</span>");
                clue_box.textfill();

                $(questions_column[j]).attr('data-value', questions_data[j].value);
                $(questions_column[j]).attr('data-category', category_data.name);
            }
        }
    }

    /**
     * Searches for a question box with a given categoryName and value, and blanks it out.
     *
     * @param categoryName
     * @param value
     */
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

    /**
     * Given a particular question box, actually clear all HTML from the box and unbind any handlers associated with it
     * @param questionBox
     */
    function clearQuestionBox(questionBox) {
        questionBox.unbind("click");
        questionBox.html("");
        questionBox.removeClass('question');
    }

    /**
     * Populates the question modal with data about the current question, and displays it to the user.
     *
     * @param question_modal
     */
    function showQuestion(question_modal)
    {
        if (question_modal == null) {
            console.error("Could not display the question - no modal defined!");
            return;
        }
        var clue = question_modal.find('.content').first().find('.clue').first();
        clue.html(question.getClue());

        if (jeopardy.admin_mode) {
            question_modal.find('.content').first().find('.answer').first().show();
            question_modal.find('.content').first().find('.answer').first().find('.content').html(question.getAnswer());
        }
        question_modal.show('fast', function() {
            clue.textfill({maxFontPixels: 150});
        });

    }

    /**
     * Clears all question data from the question modal and hides it.
     *
     * @param question_modal
     */
    function hideQuestion(question_modal)
    {
        if (question_modal == null) {
            console.error("Could not hide the question - no modal defined!");
            return;
        }

        question_modal.find('.content').first().find('.clue').first().html("");

        if (jeopardy.admin_mode) {
            question_modal.find('.content').first().find('.answer').first().hide();
            question_modal.find('.content').first().find('.answer').first().find('.content').html("");
        }

        question_modal.hide('fast');
    }

    function showDailyDouble(daily_double_modal) {
        if (daily_double_modal == null) {
            console.error("Could not show daily double - modal is not defined!");
        }
        daily_double_modal.show('fast');
    }

    function hideDailyDouble(daily_double_modal)
    {
        // Daily double betting is handled by the admin, the client doesn't need to do this.
        if (jeopardy.admin_mode) {
            $('#daily-double-bet').val("");
        }

        if (daily_double_modal == null) {
            console.error("Could not hide daily double - no modal defined!");
            return;
        }

        daily_double_modal.hide();
    }

    /**
     * Updates a given contestants score. If the optional third parameter "add" is true, then it will add the new score
     * to their current score. Otherwise, it will simply replace their current score with the new score.
     *
     * @param contestant
     * @param score
     * @param add
     */
    function updateContestantScore(contestant, score, add) {
        if (add) {
            var curScore = parseInt($('.player.' + contestant).find('.score').html());
            score = curScore + parseInt(score);
        }
        $('.player.' + contestant).find('.score').first().html(score);
    }

    return jeopardy;

    console.log("WEEEEEE");
}((window.jeopardy || {}), window.buzzer, window.question));

