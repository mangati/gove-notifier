<?php

namespace Mangati\Notifier\Gove;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

final class GoveOptions implements MessageOptionsInterface
{
	/** @param string[] */
    public function __construct(
        private readonly string $recipientId,
        private readonly string $template,
        private readonly array $variables,
    ) {}

	public function getTemplate(): string
	{
		return $this->template;
	}

	/** @return string[] */
	public function getVariables(): array
	{
		return $this->variables;
	}

	public function toArray(): array {
		return [
			'slug_template' => $this->template,
			'to' => $this->recipientId,
			'variables' => $this->variables,
		];
	}

	public function getRecipientId(): ?string
	{
		return $this->recipientId;
	}
}
