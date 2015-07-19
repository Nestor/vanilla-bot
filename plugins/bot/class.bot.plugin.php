<?php
/**
 * Share what you come up with on vanillaforums.org!
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
            $Bot->evaluateReplyTo(val('Body', $Comment));
            $Bot->say();
        }
	}

    /**
     * Set default replies.
     */
    public function structure() {
        if (!c('Bot.Priority'))
            saveToConfig('Bot.Priority', array('BotShave', 'BotMuffinMan', 'BotSendBeer'));
    }

    /**
     * Setup.
     */
    public function setup() {
        $this->structure();
    }
}