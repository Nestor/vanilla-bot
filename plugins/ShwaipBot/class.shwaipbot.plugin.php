<?php if (!defined('APPLICATION')) exit();
/**
 * IMPORTANT: Rename botreplies.dist.php to botreplies.php.
 *
 * Share what you come up with on vanillaforums.org!
 */
$PluginInfo['ShwaipBot'] = array(
	'Name' => 'ShwaipBot',
	'Description' => 'Program your System bot to reply to catch phrases and special conditions.',
	'Version' 	=>	 '1.0.1',
	'MobileFriendly' => TRUE,
	'Author' 	=>	 "Lincoln Russell",
	'AuthorEmail' => 'lincoln@icrontic.com',
	'AuthorUrl' =>	 'http://lincolnwebs.com',
   'License' => 'GNU GPL2'
);

class ShwaipBotPlugin extends Gdn_Plugin {
	/**
	 * Bot replies to new comments.
	 */
	public function PostController_AfterCommentSave_Handler($Sender, $Args) {
      if (!GetValue('Editing', $Args)) {
         $Comment = GetValue('Comment', $Args);
         $Bot = new ShwaipBot();
         $Bot->SetDiscussion(GetValue('Discussion', $Args));
         $Bot->SetInstigator(UserBuilder($Comment, 'Insert'));
         $Bot->EvaluateReplyTo(GetValue('Body', $Comment));
         $Bot->Say();
      }
	}

   /**
    * Set default replies.
    */
   public function Structure() {
      if (!C('Plugins.ShwaipBot.ReplyOrder'))
         SaveToConfig('Plugins.ShwaipBot.ReplyOrder', array('BotShave', 'BotMuffinMan', 'BotSendBeer'));
   }

   public function Setup() {
      $this->Structure();
   }
}