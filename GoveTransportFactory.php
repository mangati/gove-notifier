<?php

namespace Mangati\Notifier\Gove;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

final class GoveTransportFactory extends AbstractTransportFactory
{
    private const SCHEME = 'gove';

    /** @return GoveTransport */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if (self::SCHEME !== $scheme) {
            throw new UnsupportedSchemeException($dsn, self::SCHEME, $this->getSupportedSchemes());
        }

        $email = $this->getUser($dsn);
        $password = $this->getPassword($dsn);
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new GoveTransport($email, $password, $this->client, $this->dispatcher))
            ->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return [self::SCHEME];
    }
}
