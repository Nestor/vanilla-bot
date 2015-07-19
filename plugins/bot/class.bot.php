<?php

class Bot extends Gdn_Plugin {

    /** @var array Discussion we're in. */
    public $discussion = array();

    /** @var array User who triggered this mess. */
    public $user = array();

    /** @var int Our loveable bot's UserID. */
    protected $botID = 0;

    /** @var string What was said? */
    public $body = '';

    /** @var string What do we say back? */
    public $reply = false;

    /**
     * First positions.
     */
    public function __construct() {
        $this->botID = c('Bot.UserID', Gdn::userModel()->getSystemUserID());
    }

    /**
     * Who are we talking to?
     */
    public function setUser($user) {
        $this->user = (array) $user;
    }

    /**
     * Formatted mention of user we're interacting with.
     *
     * @return Mention of the Instigator.
     */
    public function mention() {
        return '@'.val('Name', $this->Instigator);
    }

    /**
     * Where are we & who are we talking to?
     */
    public function setDiscussion($discussion) {
        $this->discussion = (array) $discussion;
    }

    /**
     * Go on, say it.
     */
    public function say() {
        if ($this->reply) {
            $commentModel = new CommentModel();
            $botComment = array(
                'DiscussionID' => val('DiscussionID', $this->discussion),
                'InsertUserID' => $this->BotID,
                'Body' => $this->reply
            );
            $commentModel->save($botComment);
        }
    }

    /**
     * Figure out something clever to say.
     */
    public function evaluateReplyTo($body) {
        $this->body = $body;
        $priority = c('Bot.Priority');
        if (!is_array($priority))
            return;


        //fire events here


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
     * @return bool Whether trigger text contains $Text.
     */
    public function simpleMatch($text) {
        return (strpos(strtolower($this->body), $text) !== false);
    }

    /**
     * @return bool Whether trigger text matches $Pattern.
     */
    public function patternMatch($pattern, &$matches = array()) {
        return (preg_match('/'.$pattern.'/i', $this->body, $matches));
    }
}
