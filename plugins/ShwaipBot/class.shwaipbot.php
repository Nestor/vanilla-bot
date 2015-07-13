<?php if (!defined('APPLICATION')) exit();

class ShwaipBot {
   /** @var array Discussion we're in. */
   public $Discussion = array();

   /** @var array User who triggered this mess. */
   public $Instigator = array();

   /** @var int Our loveable bot's UserID. */
   protected $BotID = 0;

   /** @var string What was said? */
   public $TriggerText = '';

   /** @var string What do we say back? */
   public $Reply = FALSE;

   /**
    * First positions.
    */
   public function __construct() {
      $this->BotID = C('Plugins.Shwaipbot.UserID', Gdn::UserModel()->GetSystemUserID());
   }

   /**
    * Who are we talking to?
    */
   public function SetInstigator($User) {
      $this->Instigator = (array) $User;
   }

   /**
    * @return Mention of the Instigator.
    */
   public function Mention() {
      return '@'.GetValue('Name', $this->Instigator);
   }

   /**
    * Where are we & who are we talking to?
    */
   public function SetDiscussion($Discussion) {
      $this->Discussion = (array) $Discussion;
   }

   /**
    * Go on, say it.
    */
   public function Say() {
      if ($this->Reply) {
         $CommentModel = new CommentModel();
         $BotComment = array(
            'DiscussionID' => GetValue('DiscussionID', $this->Discussion),
            'InsertUserID' => $this->BotID,
            'Body' => $this->Reply
         );
         $CommentModel->Save($BotComment);
      }
   }

   /**
    * Figure out something clever to say.
    */
   public function EvaluateReplyTo($Body) {
      $this->TriggerText = $Body;
      $ReplyOrder = C('Plugins.ShwaipBot.ReplyOrder');
      if (!is_array($ReplyOrder))
         return;

      if (file_exists(PATH_CONF.'/botreplies.php'))
         include_once(PATH_CONF.'/botreplies.php');
      else return;

      foreach ($ReplyOrder as $ReplyName) {
         if (!function_exists($ReplyName)) {
            continue;
         }
         if ($this->Reply = $ReplyName($this)) {
            break;
         }
      }
   }

   /**
    * @return bool Whether trigger text contains $Text.
    */
   public function SimpleMatch($Text) {
      return (strpos(strtolower($this->TriggerText), $Text) !== FALSE);
   }

   /**
    * @return bool Whether trigger text matches $Pattern.
    */
   public function PatternMatch($Pattern, &$Matches = array()) {
      return (preg_match('/'.$Pattern.'/i', $this->TriggerText, $Matches));
   }
}
