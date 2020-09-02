<?php

namespace Maknz\Slack;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;

class Client {
	/**
	 * The Slack incoming webhook endpoint.
	 *
	 * @var string
	 */
	protected string $endpoint;

	/**
	 * The default channel to send messages to.
	 *
	 * @var string
	 */
	protected string $channel;

	/**
	 * The default username to send messages as.
	 *
	 * @var string
	 */
	protected string $username;

	/**
	 * The default icon to send messages with.
	 *
	 * @var string
	 */
	protected string $icon;

	/**
	 * Whether to link names like @regan or leave
	 * them as plain text.
	 *
	 * @var bool
	 */
	protected bool $link_names = false;

	/**
	 * Whether Slack should unfurl text-based URLs.
	 *
	 * @var bool
	 */
	protected bool $unfurl_links = false;

	/**
	 * Whether Slack should unfurl media URLs.
	 *
	 * @var bool
	 */
	protected bool $unfurl_media = true;

	/**
	 * Whether message text should be formatted with Slack's
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
	 * The Guzzle HTTP client instance.
	 *
	 * @var Guzzle|null
	 */
	protected ?Guzzle $guzzle;

	/**
	 * Instantiate a new Client.
	 *
	 * @param string      $endpoint
	 * @param array       $attributes
	 *
	 * @param Guzzle|null $guzzle
	 */
	public function __construct(string $endpoint, array $attributes = [], Guzzle $guzzle = null) {
		$this->endpoint = $endpoint;

		if (isset($attributes['channel'])) {
			$this->setDefaultChannel($attributes['channel']);
		}

		if (isset($attributes['username'])) {
			$this->setDefaultUsername($attributes['username']);
		}

		if (isset($attributes['icon'])) {
			$this->setDefaultIcon($attributes['icon']);
		}

		if (isset($attributes['link_names'])) {
			$this->setLinkNames($attributes['link_names']);
		}

		if (isset($attributes['unfurl_links'])) {
			$this->setUnfurlLinks($attributes['unfurl_links']);
		}

		if (isset($attributes['unfurl_media'])) {
			$this->setUnfurlMedia($attributes['unfurl_media']);
		}

		if (isset($attributes['allow_markdown'])) {
			$this->setAllowMarkdown($attributes['allow_markdown']);
		}

		if (isset($attributes['markdown_in_attachments'])) {
			$this->setMarkdownInAttachments($attributes['markdown_in_attachments']);
		}

		$this->guzzle = $guzzle ?: new Guzzle;
	}

	/**
	 * Pass any unhandled methods through to a new Message
	 * instance.
	 *
	 * @param string $name      The name of the method
	 * @param array  $arguments The method arguments
	 *
	 * @return Message
	 */
	public function __call(string $name, array $arguments) {
		return call_user_func_array([$this->createMessage(), $name], $arguments);
	}

	/**
	 * Get the Slack endpoint.
	 *
	 * @return string
	 */
	public function getEndpoint(): string {
		return $this->endpoint;
	}

	/**
	 * Set the Slack endpoint.
	 *
	 * @param string $endpoint
	 *
	 * @return void
	 */
	public function setEndpoint(string $endpoint): void {
		$this->endpoint = $endpoint;
	}

	/**
	 * Get the default channel messages will be created for.
	 *
	 * @return string
	 */
	public function getDefaultChannel(): string {
		return $this->channel;
	}

	/**
	 * Set the default channel messages will be created for.
	 *
	 * @param string $channel
	 *
	 * @return void
	 */
	public function setDefaultChannel(string $channel): void {
		$this->channel = $channel;
	}

	/**
	 * Get the default username messages will be created for.
	 *
	 * @return string
	 */
	public function getDefaultUsername(): string {
		return $this->username;
	}

	/**
	 * Set the default username messages will be created for.
	 *
	 * @param string $username
	 *
	 * @return void
	 */
	public function setDefaultUsername(string $username): void {
		$this->username = $username;
	}

	/**
	 * Get the default icon messages will be created with.
	 *
	 * @return string
	 */
	public function getDefaultIcon(): string {
		return $this->icon;
	}

	/**
	 * Set the default icon messages will be created with.
	 *
	 * @param string $icon
	 *
	 * @return void
	 */
	public function setDefaultIcon(string $icon): void {
		$this->icon = $icon;
	}

	/**
	 * Get whether messages sent will have names (like @regan)
	 * will be converted into links.
	 *
	 * @return bool
	 */
	public function getLinkNames(): bool {
		return $this->link_names;
	}

	/**
	 * Set whether messages sent will have names (like @regan)
	 * will be converted into links.
	 *
	 * @param bool $value
	 *
	 * @return void
	 */
	public function setLinkNames(bool $value): void {
		$this->link_names = $value;
	}

	/**
	 * Get whether text links should be unfurled.
	 *
	 * @return bool
	 */
	public function getUnfurlLinks(): bool {
		return $this->unfurl_links;
	}

	/**
	 * Set whether text links should be unfurled.
	 *
	 * @param bool $value
	 *
	 * @return void
	 */
	public function setUnfurlLinks(bool $value): void {
		$this->unfurl_links = $value;
	}

	/**
	 * Get whether media links should be unfurled.
	 *
	 * @return bool
	 */
	public function getUnfurlMedia(): bool {
		return $this->unfurl_media;
	}

	/**
	 * Set whether media links should be unfurled.
	 *
	 * @param bool $value
	 *
	 * @return void
	 */
	public function setUnfurlMedia(bool $value): void {
		$this->unfurl_media = $value;
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
	 * @return void
	 */
	public function setAllowMarkdown(bool $value): void {
		$this->allow_markdown = $value;
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
	 * @return void
	 */
	public function setMarkdownInAttachments(array $fields): void {
		$this->markdown_in_attachments = $fields;
	}

	/**
	 * Create a new message with defaults.
	 *
	 * @return Message
	 */
	public function createMessage(): Message {
		$message = new Message($this);

		$message->setChannel($this->getDefaultChannel());

		$message->setUsername($this->getDefaultUsername());

		$message->setIcon($this->getDefaultIcon());

		$message->setAllowMarkdown($this->getAllowMarkdown());

		$message->setMarkdownInAttachments($this->getMarkdownInAttachments());

		return $message;
	}

	public function sendMessage(Message $message): void {
		$payload = $this->preparePayload($message);

		try {
			$encoded = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		} catch (JsonException $e) {
			throw new RuntimeException($e->getMessage());
		}

		if ($encoded === false) {
			throw new RuntimeException(sprintf('JSON encoding error %s: %s', json_last_error(), json_last_error_msg()));
		}

		try {
			$this->guzzle->post($this->endpoint, ['body' => $encoded]);
		} catch (GuzzleException $e) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * Prepares the payload to be sent to the webhook.
	 *
	 * @param Message $message The message to send
	 *
	 * @return array
	 */
	public function preparePayload(Message $message): array {
		$payload = [
			'text'         => $message->getText(),
			'channel'      => $message->getChannel(),
			'username'     => $message->getUsername(),
			'link_names'   => $this->getLinkNames() ? 1 : 0,
			'unfurl_links' => $this->getUnfurlLinks(),
			'unfurl_media' => $this->getUnfurlMedia(),
			'mrkdwn'       => $message->getAllowMarkdown(),
		];

		if ($icon = $message->getIcon()) {
			$payload[$message->getIconType()] = $icon;
		}

		$payload['attachments'] = $this->getAttachmentsAsArrays($message);

		return $payload;
	}

	/**
	 * Get the attachments in array form.
	 *
	 * @param Message $message
	 *
	 * @return array
	 */
	protected function getAttachmentsAsArrays(Message $message): array {
		$attachments = [];

		foreach ($message->getAttachments() as $attachment) {
			$attachments[] = $attachment->toArray();
		}

		return $attachments;
	}
}
