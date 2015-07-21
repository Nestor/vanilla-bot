<?php
/**
 * @copyright 2015 Lincoln Russell
 * @license GNU GPL2
 * @package Bot
 */

/**
 * Class Bot
 */
class Bot extends Gdn_Plugin {

    /** @var array Discussion we're in. */
    protected $discussion = array();

    /** @var array User who triggered this mess. */
    protected $user = array();

    /** @var int Our loveable bot's UserID. */
    protected $botID = 0;

    /** @var string What was said? */
    protected $body = '';

    /** @var string What triggered this? */
    protected $context = 'comment';

    /** @var string What do we say back? */
    protected $reply = false;

    /**
     * First positions.
     */
    public function __construct() {
        $this->botID = c('Bot.UserID', Gdn::userModel()->getSystemUserID());
    }

    /**
     * Fire an (reply) event from the Bot context.
     *
     * @param $name
     */
    public function fireReply($name) {
        $this->fireEvent($name);
    }

    /**
     * Formatted mention of user we're interacting with.
     *
     * @return Mention of the user who triggered this.
     */
    public function mention() {
        return '@'.val('Name', $this->user);
    }

    /**
     * Do a regex match on the body.
     *
     * @return bool Whether trigger text matches $Pattern.
     */
    public function regex($pattern, &$matches = array()) {
        return (preg_match('/'.$pattern.'/i', $this->body, $matches));
    }

    /**
     * Go on, say it.
     */
    public function say() {
        if ($this->reply) {
            $commentModel = new CommentModel();
            $botComment = array(
                'DiscussionID' => val('DiscussionID', $this->discussion),
                'InsertUserID' => $this->botID,
                'Body' => $this->reply
            );
            $commentModel->save($botComment);
        }
    }

    /**
     * What was said?
     */
    public function setBody($body) {
        $this->body = (string) $body;
    }

    /**
     * What was said?
     */
    public function setContext($context) {
        if (in_array($context, array('discussion', 'comment'))) { //, 'wallpost', 'wallcomment'
            $this->context = $context;
        }
    }

    /**
     * Where are we & who are we talking to?
     */
    public function setDiscussion($discussion) {
        $this->discussion = (array) $discussion;
    }

    /**
     * What are we gonna say?
     */
    public function setReply($reply) {
        $this->reply = (string) $reply;
    }

    /**
     * Who are we talking to?
     */
    public function setUser($user) {
        $this->user = (array) $user;
    }

    /**
     * Do a simple text match on the body.
     *
     * @return bool Whether trigger text contains $Text.
     */
    public function match($text) {
        return (strpos(strtolower($this->body), $text) !== false);
    }
}
