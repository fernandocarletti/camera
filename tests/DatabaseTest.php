<?php

declare(strict_types=1);

namespace App\Tests;

use App\Database;
use App\Exception\DomainException;
use App\VeSync\Token;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class DatabaseTest extends TestCase
{
    public function testIsStoringToken(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $client->set('vesync:account:foo', '{"accountId":"foo","token":"bar","secret":"zaz"}')->shouldBeCalled();

        $database = new Database($client->reveal());

        $database->storeToken(new Token('foo', 'bar', 'zaz'));
    }

    public function testIsRetrievingToken(): void
    {
        $expected = new Token('foo', 'bar', 'zaz');

        $client = $this->prophesize(ClientInterface::class);
        $client->get('vesync:account:foo')->shouldBeCalled()->willReturn('{"accountId":"foo","token":"bar","secret":"zaz"}');

        $database = new Database($client->reveal());

        $actual = $database->retrieveToken('foo');

        $this->assertEquals($expected, $actual);
    }

    public function testIsHandlingInexistentToken(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Token not found.');

        $client = $this->prophesize(ClientInterface::class);
        $client->get('vesync:account:foo')->shouldBeCalled()->willReturn(null);

        $database = new Database($client->reveal());

        $database->retrieveToken('foo');
    }
}
