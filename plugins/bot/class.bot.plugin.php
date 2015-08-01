<?php
/**
 * @copyright 2015 Lincoln Russell
 * @license GNU GPL2
 * @package Bot
 */

$PluginInfo['bot'] = array(
	'Name' => 'Bot',
	'Description' => 'Program your bot to reply to catch phrases and special conditions.',
	'Version' 	=>	 '1.0',
	'MobileFriendly' => TRUE,
	'Author' 	=>	 "Lincoln Russell",
	'AuthorEmail' => 'lincoln@icrontic.com',
	'AuthorUrl' =>	 'http://lincolnwebs.com',
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
        $replies = Gdn::get('bot.replies.%');
        asort($replies);

        // Process all possible replies.
        foreach ($replies as $metaName => $priority) {
            // Extract function name from the key.
            $eventName = str_replace('bot.replies.', '', $metaName);

            // Call bot event handler.
            $bot->fireReply($eventName);

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

/**
 * Add a reply to the call stack.
 *
 * @param $name
 * @param $priority
 */
function botReply($eventName, $priority = false) {
    // If no priority is set, automatically increment it in the order received.
    static $defaultPriority;
    if (!isset($defaultPriority)) {
        $defaultPriority = 0;
    }
    if (!$priority) {
        // Next consecutive priority.
        $priority = $defaultPriority++;
    } elseif ($priority > $defaultPriority) {
        // Fast forward our default so that it will be highest existing priority +1.
        $defaultPriority = $priority;
    }

    // Register our reply.
    Gdn::set('bot.replies.'.$eventName, $priority);
}