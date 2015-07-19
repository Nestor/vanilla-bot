<?php if (!defined('APPLICATION')) exit();

/**
 * 1) Rename this file to botreplies.php and move it to your conf folder.
 * 2) Use the examples below to create your own bot reply functions. Return a string (on trigger) or FALSE.
 * 3) Set Plugins.ShwaipBot.ReplyOrder to an array of the function names, in order of precedence.
 *     Default: $Configuration['Plugins']['ShwaipBot']['ReplyOrder'] = array('BotShave', 'BotMuffinMan', 'BotSendBeer');
 *     If someone says something that triggers multiple replies, only the first will fire.
 */

/**
 * Simple call and response.
 *
 * User: Shave and a hair cut!
 * Bot: TWO BITS!
 */
function BotShave($Bot) {
    if ($Bot->SimpleMatch('shave and a hair cut'))
        return $Bot->Mention().' TWO BITS!';
}

/**
 * Replicates Gingerbread Man interrogation in Shrek.
 *
 * User: [mentions 'muffin man']
 * Bot: Do you know the muffin man?
 * User: The muffin man?
 * Bot: THE MUFFIN MAN. Do you know the muffin man?
 * User: That lives on Drury Lane?
 * Bot: THE. SAME.
 */
function BotMuffinMan($Bot) {
    if ($Bot->SimpleMatch('muffin man')) {
        $MuffinReply = ' Do you know the muffin man?';
        if ($Bot->SimpleMatch('muffin man?'))
            $MuffinReply = ' THE MUFFIN MAN.'.$MuffinReply;
        return $Bot->Mention().$MuffinReply;
    }
    elseif($Bot->SimpleMatch('who lives on drury lane')) {
        return $Bot->Mention().' THE. SAME.';
    }
}

/**
 * Let users send each other beers thru the bot.
 *
 * User: !beer @Lincoln
 * Bot:  /me slides @Lincoln a beer.
 */
function BotSendBeer($Bot) {
    if ($Bot->PatternMatch('(^|[\s,\.>])\!beer\s@(\w{1,50})\b', $BeerWho))
        return '/me slides @'.GetValue(2, $BeerWho).' a beer.';
}