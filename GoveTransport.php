<?php

namespace Mangati\Notifier\Gove;

use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Notifier\Exception\MissingRequiredOptionException;


final class GoveTransport extends AbstractTransport
{
    protected const HOST = 'api.gove.digital';

    public function __construct(
        private readonly string $email,
        private readonly string $password,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$this->supports($message)) {
            throw new UnsupportedMessageTypeException(__CLASS__, ChatMessage::class, $message);
        }

        $options = $message->getOptions();
        if (!$options instanceof GoveOptions || null === $options->getTemplate()) {
            throw new MissingRequiredOptionException('template');
        }

        $this->sendWhatsappMessage(
            token: $this->authenticate(),
            recipient: $message->getRecipientId(),
            template: $options->getTemplate(),
            variables: $options->getVariables(),
        );

        $sentMessage = new SentMessage($message, (string) $this);

        return $sentMessage;
    }

    private function authenticate(): string
    {
        $data = $this->doRequest('oauth/token', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        return $data['token'];
    }

    private function sendWhatsappMessage(string $token, string $recipient, string $template, array $variables): void
    {
        $this->doRequest('messages/whatsapp/send', [
            'slug_template' => $template,
            'to' => $recipient,
            'variables' => $variables,
        ], [
            'Authorization' => sprintf('Bearer %s', $token),
        ]);
    }

    private function doRequest(string $path, array $body, array $headers = []): array
    {
        $endpoint = sprintf('https://%s/api/%s', $this->getEndpoint(), $path);
        $response = $this->client->request('POST', $endpoint, [
            'headers' => $headers,
            'json' => $body,
        ]);

        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Gove server.', $response, 0, $e);
        }

        if (200 !== $statusCode) {
            $data = $response->toArray(false);
            throw new TransportException('Unable to send the WhatsApp message: ' . json_encode($data), $response);
        }

        $data = $response->toArray(false);
        if ($data['success'] !== true) {
            throw new TransportException('Not successfuly response received: ' . json_encode($data), $response);
        }

        return $data;
    }

    public function __toString(): string
    {
        return sprintf('gove://%s', $this->getEndpoint());
    }
}
