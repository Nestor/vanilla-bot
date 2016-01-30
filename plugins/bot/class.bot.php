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
     * What was said?
     *
     * @param string $body Set the post body for reference (optional).
     * @return Content of post that triggered this.
     */
    public function body($body = '') {
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
     * Whether a reply has been set by a bot reply handler.
     *
     * @return bool Whether a reply has been set.
     */
    public function hasReply() {
        return ($this->reply !== false) ? true : false;
    }

    /**
     * Do a simple text match on the body.
     *
     * @param string $text Text to match.
     * @return bool Whether trigger text contains $Text.
     */
    public function match($text) {
        return (strpos(strtolower($this->body), strtolower($text)) !== false);
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
     * Show Bot as online (via Who's Online).
     *
     * @return Bot
     */
    public function online() {
        if (!class_exists('WhosOnlinePlugin')) {
            return;
        }
        $now = Gdn_Format::toDateTime();
        $px = Gdn::sql()->Database->DatabasePrefix;
        $botID = $this->botID();
        $sql = "insert {$px}Whosonline (UserID, Timestamp, Invisible) values ({$botID}, :Timestamp, :Invisible)
            on duplicate key update Timestamp = :Timestamp1, Invisible = :Invisible1";
        Gdn::database()->query($sql, array(':Timestamp' => $now, ':Invisible' => 0, ':Timestamp1' => $now, ':Invisible1' => 0));
        return $this;
    }

    /**
     * Randomize a reply from a list ($options).
     *
     * This is better than just using rand, because we track previous answers and avoid repeats.
     * 70% of possible answers will be stored as a "no repeat" log that cycles.
     *
     * @param string $event Slug.
     * @param array $options Numeric array of string replies.
     * @return string Reply.
     */
    public function randomize($event, $options) {
        // Get what he's said recently
        $previous = $this->state($event, array());
        $total = count($options);
        // Always make sure 30% isn't in the log so it's selectable.
        $limit = round($total * .7);

        // Randomize an option, but skip recently used ones.
        do {
            $get = rand(0, $total-1);
        } while (in_array($get, $previous));

        // Store what we chose & say it.
        $previous = array_merge(array($get), array_slice($previous, 0, $limit));
        $this->setState($event, $previous);

        return val($get, $options);
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
     * Save the current reply as a new comment in the discussion.
     *
     * @return Bot
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
        return $this;
    }

    /**
     * What are we gonna say?
     *
     * Set to `true` to have no reply at all and skip further reply checks.
     *
     * @param string $reply Fully formatted post in set format.
     * @return Bot
     */
    public function setReply($reply) {
        $this->reply = ($reply !== true) ? (string) $reply : true;
        return $this;
    }

    /**
     * Set a state/value into the database.
     *
     * @param $event Slug. 50 characters max. Avoid spaces.
     * @param null $value
     * @param int $userid
     * @return Bot
     */
    public function setState($event, $value = null, $userid = 0) {
        $event = substr(strolower($event), 0, 50);
        Gdn::userModel()->setMeta($userid, array($event => $value), 'bot.state.');
        return $this;
    }

    /**
     * Get a state/value in the database.
     *
     * @param $event Slug. 50 characters max. Avoid spaces.
     * @param $default Default value to return if none is set.
     * @param int $userid Use zero for global states.
     * @return int|string|bool
     */
    public function state($event, $default = null, $userid = 0) {
        $event = substr(strolower($event), 0, 50);
        return Gdn::userModel()->getMeta($userid, $event, 'bot.state.', $default);
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
}
