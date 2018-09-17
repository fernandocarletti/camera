<?php

declare(strict_types=1);

namespace App\Tests;

use App\Database;
use App\Exception\DomainException;
use App\VeSync\Device;
use App\VeSync\Pool;
use App\VeSync\Token;
use PHPUnit\Framework\TestCase;
use Predis\ClientInterface;

class DatabaseTest extends TestCase
{
    public function testIsStoringToken(): void
    {
        $client = $this->prophesize(ClientInterface::class);
        $client->set('vesync:account:foo', '{"accountId":"foo","token":"bar","isAway":false,"secret":"zaz"}')->shouldBeCalled();

        $database = new Database($client->reveal());

        $database->storeToken(new Token('foo', 'bar', false, 'zaz'));
    }

    public function testIsRetrievingToken(): void
    {
        $expected = new Token('foo', 'bar', true, 'zaz');

        $client = $this->prophesize(ClientInterface::class);
        $client->get('vesync:account:foo')->shouldBeCalled()->willReturn('{"accountId":"foo","token":"bar","isAway":true,"secret":"zaz"}');

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

    public function testIsRetrievingTokens(): void
    {
        $expected = [
            new Token('foo', 'bar', false, 'zaz'),
            new Token('foz', 'baz', true, 'zar'),
        ];

        $client = $this->prophesize(ClientInterface::class);
        $client->mget([
            'vesync:account:foo',
            'vesync:account:foz',
            'vesync:account:bar',
        ])
            ->shouldBeCalled()
            ->willReturn([
                '{"accountId":"foo","token":"bar","isAway":false,"secret":"zaz"}',
                '{"accountId":"foz","token":"baz","isAway":true,"secret":"zar"}',
                false,
            ]);

        $database = new Database($client->reveal());

        $actual = $database->retrieveTokens(['foo', 'foz', 'bar']);

        $this->assertEquals($expected, $actual);
    }

    public function testIsRetrievingPool(): void
    {
        $expected = new Pool('abc123');
        $expected->addToken(new Token('foo', 'bar', false, 'zaz'));
        $expected->addToken(new Token('fiz', 'baz', true, 'zar'));

        $device = new Device('abc123', 'foo', true);

        $client = $this->prophesize(ClientInterface::class);
        $client->get('vesync:pool:abc123')->shouldBeCalled()->willReturn('{"accountIds":["foo","fiz"]}');
        $client->mget([
            'vesync:account:foo',
            'vesync:account:fiz',
        ])
            ->shouldBeCalled()
            ->willReturn([
                '{"accountId":"foo","token":"bar","isAway":false,"secret":"zaz"}',
                '{"accountId":"fiz","token":"baz","isAway":true,"secret":"zar"}',
            ]);

        $database = new Database($client->reveal());

        $actual = $database->retrievePool($device);

        $this->assertEquals($expected, $actual);
    }
}
