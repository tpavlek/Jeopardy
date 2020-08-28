window.question = (function(question) {

    var q = {};

    question.hasQuestion = function()
    {
        return q !== {};
    };

    question.isDailyDouble = function()
    {
        return q.hasOwnProperty('daily_double') && q.daily_double;
    };

    question.isImageClue = function()
    {
        return q.hasOwnProperty('type') && q.type === 'img';
    };

    question.isVideoClue = function()
    {
        return q.hasOwnProperty('type') && q.type === 'video';
    };

    question.getClue = function()
    {
        if (question.isImageClue()) {
            return "<img src='" + q.clue + "' />"
        }
        if (question.isVideoClue()) {
            return '<iframe width="560" height="315" src="' + q.clue + '"></iframe>';
        }

        return "<span>" + q.clue + "</span>";
    };

    question.getAnswer = function()
    {
        return q.answer;
    };

    question.getCategory = function()
    {
        return q.category;
    };

    question.getQuestionValue = function()
    {
        return q.value;
    };

    question.getBetValue = function()
    {
        if (q.hasOwnProperty("bet")) {
            return q.bet;
        }

        // if there is no bet present, we will simply return the actual question value as the amount "wagered"
        return question.getQuestionValue();
    };

    question.setQuestion = function(ques)
    {
        if (!ques.hasOwnProperty("clue")) {
            console.error("Tried to set a question without a clue");
            return;
        }
        q = ques;
    };

    question.clear = function()
    {
        q = {};
    };

    question.setBet = function(bet)
    {
        q.bet = bet;
    };

    return question;
}(window.question || {}));
