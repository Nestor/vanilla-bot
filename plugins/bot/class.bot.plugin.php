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
	public function postController_afterCommentSave_handler($Sender, $Args) {
        if (!val('Editing', $Args)) {
            $Comment = val('Comment', $Args);
            $Bot = new Bot();
            $Bot->setDiscussion(val('Discussion', $Args));
            $Bot->setUser(userBuilder($Comment, 'Insert'));
            $this->evaluateReplies(val('Body', $Comment));
            $Bot->say();
        }
	}

	/**
     * Figure out something clever to say.
     */
    public function evaluateReplies($body) {
        $this->body = $body;

        // Gdn::get('bot.replies');

        //fire events here
        // while ($result == false) {
        // $result = $bot->trigger('name');

        foreach ($priority as $replyName) {
            if (!function_exists($replyName)) {
                continue;
            }
            if ($this->reply = $replyName($this)) {
                break;
            }
        }
    }

    /**
     * No setup.
     */
    public function setup() {

    }
}

/**
 * @param $name
 * @param $priority
 */
function botReply($name, $priority) {

}