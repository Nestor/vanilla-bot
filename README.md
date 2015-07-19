# Bot for Vanilla

Sometimes you want a [minion](https://github.com/vanilla/minion) to do your dirty work, but sometimes you want a _personality_ to make your community management more fun. This project is for the latter.

More fun than sock puppet accounts, Bot is a tool for kickstarting a new community or bringing an old one together with a shared experience and knowledge.

Bot has customizable triggers that allow it to participate and take actions in your community as a (bot) member.

## Using Bot

You assign a priority order for events. After any event handler returns `true`, the rest are skipped (essentially a `break` for the event firing after a post). Generally this indicates the bot (i.e. that handler) has posted a comment. By returning `true`, you prevent multiple bot posts in a row.

In your `structure()` method, set your replies with their priority level:

`botReply($eventName, $priority = 100);`

If no priority is given, they are prioritized in the order they are set.

Events are thrown by the Bot object. Therefore in your plugin, event handlers for Bot are given the bot instance as `$sender`. All the relevant contextual data is available as properties of the bot.

## Design considerations

* All reply events are fired on every new post until a `true` is returned by one of them. Complex conditions or computations on a busy site could overburden your server.
* Create unique event names so they do not overlap with other plugins.
* Multiple replies may have the same priority. They will be triggered in the order declared, which may be random between plugins.