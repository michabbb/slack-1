<?php

namespace Maknz\Slack;

use InvalidArgumentException;

class Message {
	/**
	 * Reference to the Slack client responsible for sending
	 * the message.
	 *
	 * @var Client
	 */
	protected Client $client;

	/**
	 * The text to send with the message.
	 *
	 * @var string
	 */
	protected string $text;

	/**
	 * The channel the message should be sent to.
	 *
	 * @var string
	 */
	protected string $channel;

	/**
	 * The username the message should be sent as.
	 *
	 * @var string
	 */
	protected string $username;

	/**
	 * The URL to the icon to use.
	 *
	 * @var string
	 */
	protected string $icon;

	/**
	 * The type of icon we are using.
	 *
	 * @var string
	 */
	protected string $iconType;

	/**
	 * Whether the message text should be interpreted in Slack's
	 * Markdown-like language.
	 *
	 * @var bool
	 */
	protected bool $allow_markdown = true;

	/**
	 * The attachment fields which should be formatted with
	 * Slack's Markdown-like language.
	 *
	 * @var array
	 */
	protected array $markdown_in_attachments = [];

	/**
	 * An array of attachments to send.
	 *
	 * @var array
	 */
	protected array $attachments = [];

	/**
	 * @var string
	 */
	public const ICON_TYPE_URL = 'icon_url';

	/**
	 * @var string
	 */
	public const ICON_TYPE_EMOJI = 'icon_emoji';

	/**
	 * Instantiate a new Message.
	 *
	 * @param Client $client
	 *
	 * @return void
	 */
	public function __construct(Client $client) {
		$this->client                  = $client;
		$this->text                    = '';
		$this->icon                    = '';
		$this->markdown_in_attachments = [];
		$this->username                = '';
		$this->channel                 = '';
		$this->allow_markdown          = true;
		$this->attachments             = [];
		$this->iconType                = '';
	}

	/**
	 * Get the message text.
	 *
	 * @return string
	 */
	public function getText(): string {
		return $this->text;
	}

	/**
	 * Set the message text.
	 *
	 * @param string $text
	 *
	 * @return $this
	 */
	public function setText(string $text): self {
		$this->text = $text;

		return $this;
	}

	/**
	 * Get the channel we will post to.
	 *
	 * @return string
	 */
	public function getChannel(): string {
		return $this->channel;
	}

	/**
	 * Set the channel we will post to.
	 *
	 * @param string $channel
	 *
	 * @return $this
	 */
	public function setChannel(string $channel): self {
		$this->channel = $channel;

		return $this;
	}

	/**
	 * Get the username we will post as.
	 *
	 * @return string
	 */
	public function getUsername(): string {
		return $this->username;
	}

	/**
	 * Set the username we will post as.
	 *
	 * @param string $username
	 *
	 * @return $this
	 */
	public function setUsername(string $username): self {
		$this->username = $username;

		return $this;
	}

	/**
	 * Get the icon (either URL or emoji) we will post as.
	 *
	 * @return string
	 */
	public function getIcon(): string {
		return $this->icon;
	}

	/**
	 * Set the icon (either URL or emoji) we will post as.
	 *
	 * @param string $icon
	 *
	 * @return Message|void
	 */
	public function setIcon(string $icon) {
		if ($icon === null) {
			/** @noinspection PhpFieldAssignmentTypeMismatchInspection */
			$this->icon = $this->iconType = null;

			return;
		}

		if (mb_strpos($icon, ':') === 0 && mb_substr($icon, mb_strlen($icon) - 1, 1) === ':') {
			$this->iconType = self::ICON_TYPE_EMOJI;
		} else {
			$this->iconType = self::ICON_TYPE_URL;
		}

		$this->icon = $icon;

		return $this;
	}

	/**
	 * Get the icon type being used, if an icon is set.
	 *
	 * @return string
	 */
	public function getIconType(): string {
		return $this->iconType;
	}

	/**
	 * Get whether message text should be formatted with
	 * Slack's Markdown-like language.
	 *
	 * @return bool
	 */
	public function getAllowMarkdown(): bool {
		return $this->allow_markdown;
	}

	/**
	 * Set whether message text should be formatted with
	 * Slack's Markdown-like language.
	 *
	 * @param bool $value
	 *
	 * @return Message
	 */
	public function setAllowMarkdown(bool $value): Message {
		$this->allow_markdown = $value;

		return $this;
	}

	/**
	 * Enable Markdown formatting for the message.
	 *
	 * @return Message
	 */
	public function enableMarkdown(): Message {
		$this->setAllowMarkdown(true);

		return $this;
	}

	/**
	 * Disable Markdown formatting for the message.
	 *
	 * @return Message
	 */
	public function disableMarkdown(): Message {
		$this->setAllowMarkdown(false);

		return $this;
	}

	/**
	 * Get the attachment fields which should be formatted
	 * in Slack's Markdown-like language.
	 *
	 * @return array
	 */
	public function getMarkdownInAttachments(): array {
		return $this->markdown_in_attachments;
	}

	/**
	 * Set the attachment fields which should be formatted
	 * in Slack's Markdown-like language.
	 *
	 * @param array $fields
	 *
	 * @return Message
	 */
	public function setMarkdownInAttachments(array $fields): Message {
		$this->markdown_in_attachments = $fields;

		return $this;
	}

	/**
	 * Change the name of the user the post will be made as.
	 *
	 * @param string $username
	 *
	 * @return $this
	 */
	public function from(string $username): self {
		$this->setUsername($username);

		return $this;
	}

	/**
	 * Change the channel the post will be made to.
	 *
	 * @param string $channel
	 *
	 * @return $this
	 */
	public function to(string $channel): self {
		$this->setChannel($channel);

		return $this;
	}

	/**
	 * Chainable method for setting the icon.
	 *
	 * @param string $icon
	 *
	 * @return $this
	 */
	public function withIcon(string $icon): self {
		$this->setIcon($icon);

		return $this;
	}

	/**
	 * Add an attachment to the message.
	 *
	 * @param mixed $attachment
	 *
	 * @return $this
	 */
	public function attach($attachment): self {
		if ($attachment instanceof Attachment) {
			$this->attachments[] = $attachment;

			return $this;
		}

		if (is_array($attachment)) {
			$attachmentObject = new Attachment($attachment);

			if (!isset($attachment['mrkdwn_in'])) {
				$attachmentObject->setMarkdownFields($this->getMarkdownInAttachments());
			}

			$this->attachments[] = $attachmentObject;

			return $this;
		}

		throw new InvalidArgumentException('Attachment must be an instance of Maknz\\Slack\\Attachment or a keyed array');
	}

	/**
	 * Get the attachments for the message.
	 *
	 * @return array
	 */
	public function getAttachments(): array {
		return $this->attachments;
	}

	/**
	 * Set the attachments for the message.
	 *
	 * @param array $attachments
	 *
	 * @return $this
	 */
	public function setAttachments(array $attachments): self {
		$this->clearAttachments();

		foreach ($attachments as $attachment) {
			$this->attach($attachment);
		}

		return $this;
	}

	/**
	 * Remove all attachments for the message.
	 *
	 * @return $this
	 */
	public function clearAttachments(): self {
		$this->attachments = [];

		return $this;
	}

	/**
	 * Send the message.
	 *
	 * @param null $text The text to send
	 *
	 * @return Message
	 */
	public function send($text = null): Message {
		if ($text) {
			$this->setText($text);
		}

		$this->client->sendMessage($this);

		return $this;
	}
}
