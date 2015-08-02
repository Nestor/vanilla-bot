<?php
/**
 * @copyright 2015 Lincoln Russell
 * @license GNU GPL2
 * @package Bot
 */

$PluginInfo['bot'] = array(
	'Name' => 'Bot',
	'Description' => 'Program your own bot to reply to catch phrases and special conditions.',
	'Version' 	=> '1.1',
	'MobileFriendly' => true,
	'Author' => "Lincoln Russell",
	'AuthorEmail' => 'lincoln@icrontic.com',
	'AuthorUrl' => 'http://lincolnwebs.com',
    'License' => 'GNU GPL2'
);

/**
 * Share what you come up with on vanillaforums.org!
 */
class BotPlugin extends Gdn_Plugin {

	/**
	 * Bot replies to new comments.
	 */
	public function postController_afterCommentSave_handler($sender, $args) {
        if (!val('Editing', $args)) {
            $post = val('Comment', $args);
            $bot = new Bot();
            $bot->context('comment');
            $bot->discussion(val('Discussion', $args));
            $bot->user(userBuilder($post, 'Insert'));
            $bot->body(val('Body', $post));
            $this->doReplies($bot);
        }
	}

	/**
	 * Bot replies to new discussions.
	 */
	public function postController_afterDiscussionSave_handler($sender, $args) {
        if (!val('Editing', $args)) {
            $post = val('Discussion', $args);
            $bot = new Bot();
            $bot->context('discussion');
            $bot->discussion(val('Discussion', $args));
            $bot->user(userBuilder($post, 'Insert'));
            $bot->body(val('Body', $post));
            $this->doReplies($bot);
        }
	}

	/**
     * Figure out something clever to say.
     *
     * @param Bot $bot
     */
    public function doReplies($bot) {
        // Get all replies that have been registered.
        //$replies = Gdn::get('bot.replies.%'); // This is bugged.
        $replies = Gdn::userModel()->getMeta(0, 'bot.replies.%', 'bot.replies.');
        asort($replies);

        // Process all possible replies.
        foreach ($replies as $eventName => $priority) {
            // Call bot event handler.
            $bot->fireEvent($eventName);

            // If that event set a reply, let's move on.
            if ($bot->hasReply()) {
                $bot->say();
                return true;
            }
        }
    }

    /**
     * No setup.
     */
    public function setup() { }
}

if (!function_exists('botReply')) :
/**
 * Add a reply to the call stack.
 *
 * @param $name
 * @param $priority
 */
function botReply($eventName, $priority = false) {
    // If no priority is set, automatically increment it in the order received.
    static $defaultPriority = 0;
    if (!$priority) {
        // Next consecutive priority.
        $defaultPriority++;
        $priority = $defaultPriority;
    } elseif ($priority > $defaultPriority) {
        // Fast forward our default so that it will be highest existing priority +1.
        $defaultPriority = $priority;
    }

    // Register our reply.
    // Gdn::set('bot.replies.'.$eventName, $priority); // This is bugged. #2923
    Gdn::userModel()->setMeta(0, array($eventName => $priority), 'bot.replies.');
}
endif;

if (!function_exists('botReplyDisable')) :
/**
 * Unregister a reply event.
 *
 * @param $eventName
 */
function botReplyDisable($eventName) {
    Gdn::set('bot.replies.'.$eventName, null);
}
endif;
