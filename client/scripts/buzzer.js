window.buzzer = (function(buzzer) {
    /* This is our buzzer. It can be active or inactive, and includes the time at which it became active */

    var active_at = false;
    var penalty_amount = 500;
    var penalty_until = Date.now() - 1;
    /** If the user has already buzzed in for this round, since we don't want to keep sending events to the server after the first */
    var already_buzzed = false;

    buzzer.clear = function()
    {
        active_at = false;
        penalty_until = Date.now() - 1;
        already_buzzed = false;
    };

    /**
     * Is our buzzer currently active?
     * @returns {boolean}
     */
    buzzer.isActive = function() { return active_at !== false };

    /**
     * Attempt a buzz and get the difference in time from active to buzz.
     * If the buzzer is not active, it will penalize, and return false.
     * @returns {int|boolean}
     */
    buzzer.buzz = function()
    {
        if (already_buzzed) {
            return true;
        }

        if (!buzzer.isActive()) {
            buzzer.penalize();
            return false;
        }

        var now = Date.now();
        if (penalty_until > now) {
            return (now - active_at) + penalty_amount
        }

        already_buzzed = true;
        return (now - active_at);
    };

    /**
     * Activate a penalty on the buzzer.
     */
    buzzer.penalize = function()
    {
        penalty_until = Date.now() + penalty_amount;
    };

    /**
     * Checks if the buzzer current has an active penalty.
     * @returns {boolean}
     */
    buzzer.hasActivePenalty = function()
    {
        return (penalty_until > Date.now());
    };

    buzzer.resetPenalty = function()
    {
        penalty_until = Date.now() - 1;
    };

    /**
     * Set the buzzer status to active.
     */
    buzzer.activate = function(buzzer_indicator)
    {
        active_at = Date.now();

        if (buzzer_indicator != null) {
            buzzer_indicator.removeClass('inactive-buzzer').addClass('active-buzzer');
        }
    };

    /**
     * Set the buzzer status to inactive.
     */
    buzzer.deactivate = function(buzzer_indicator)
    {
        buzzer.clear();

        // If we were passed a buzzer indicator, we should set it to inactive. If no buzzer indicator exists, we're fine
        // anyway.
        if (buzzer_indicator != null) {
            buzzer_indicator.removeClass('active-buzzer').addClass('inactive-buzzer');
        }
    };

    return buzzer;

}(window.buzzer || {}));
