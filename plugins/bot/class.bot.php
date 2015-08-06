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

    /** @var string What do we say back? */
    protected $format = 'Markdown';

    /**
     * First positions.
     */
    public function __construct() {
        parent::__construct();
        $this->botID = c('Bot.UserID', Gdn::userModel()->getSystemUserID());
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
     * @param string $pattern Regex pattern.
     * @param array $matches See preg_match().
     * @return bool Whether trigger text matches $Pattern.
     */
    public function regex($pattern, &$matches = array()) {
        return (preg_match('/'.$pattern.'/i', $this->body, $matches));
    }

    /**
     * Go on, say it.
     */
    public function say() {
        if ($this->reply && $this->reply !== true) {
            $commentModel = new CommentModel();
            $botComment = array(
                'DiscussionID' => val('DiscussionID', $this->discussion),
                'InsertUserID' => $this->botID,
                'Format' => $this->format,
                'Body' => $this->reply
            );
            $commentModel->save($botComment);
        }
        $this->fireEvent('afterSay');
    }

    /**
     * What was said?
     *
     * @param string $body Set the post body for reference (optional).
     * @return Content of post that triggered this.
     */
    public function body($body = '', $format = '') {
        if ($body != '') {
            $this->body = (string) $body;
        }
        return $this->body;
    }

    /**
     * What's the userid of our bot?
     *
     * @param string $body Set the botid (optional).
     * @return int Current botid.
     */
    public function botID($botID = '') {
        if ($botID != '') {
            $this->botID = (string) $botID;
        }
        return $this->botID;
    }

    /**
     * Where was it said?
     *
     * @param string $context Context of the body being replied to (optional).
     * @return string One of: discussion, comment.
     */
    public function context($context = '') {
        if (in_array($context, array('discussion', 'comment'))) { //, 'wallpost', 'wallcomment'
            $this->context = $context;
        }
        return $this->context;
    }

    /**
     * Where are we & who are we talking to?
     *
     * @param array $discussion Set discussion (optional).
     * @return array Discussion data.
     */
    public function discussion($discussion = array()) {
        if ((is_array($discussion) || is_object($discussion)) && count($discussion)) {
            $this->discussion = (array) $discussion;
        }
        return $this->discussion;
    }

    /**
     * What formatting engine to use on our reply. Default is Markdown.
     *
     * @param string $format One of: Html, BBCode, Markdown, TextEx, Wysiwyg, Text (optional).
     * @return string Format being used.
     */
    public function format($format = '') {
        if ($format != '') {
            $this->format = $format;
        }
        return $this->format;
    }

    /**
     * What are we gonna say?
     *
     * Set to `true` to have no reply at all and skip further reply checks.
     *
     * @param string $reply Fully formatted post in set format.
     */
    public function setReply($reply) {
        $this->reply = ($reply !== true) ? (string) $reply : true;
    }

    /**
     * Whether a reply has been set by a bot reply handler.
     *
     * @return bool Whether a reply has been set.
     */
    public function hasReply() {
        return ($this->reply !== false) ? true : false;
    }

    /**
     * Who are we talking to?
     *
     * @param array $user User info (optional).
     * @return array User info.
     */
    public function user($user = array()) {
        if ((is_array($user) || is_object($user)) && count($user)) {
            $this->user = (array) $user;
        }
        return $this->user;
    }

    /**
     * Do a simple text match on the body.
     *
     * @param string $text Text to match.
     * @return bool Whether trigger text contains $Text.
     */
    public function match($text) {
        return (strpos(strtolower($this->body), $text) !== false);
    }
}
