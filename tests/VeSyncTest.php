<?php

declare(strict_types=1);

namespace App\Tests;

use App\VeSync;
use App\VeSync\Device;
use App\VeSync\Exception\VeSyncException;
use App\VeSync\Token;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class VeSyncTest extends TestCase
{
    public function testIsLoggingIn(): void
    {
        $expected = new Token('123', 'iddqd', false);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'accountID' => '123',
                'tk' => 'iddqd',
            ])),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $actual = $veSync->login('foo', 'bar');

        $this->assertEquals($expected->getAccountId(), $actual->getAccountId());
        $this->assertEquals($expected->getToken(), $actual->getToken());
    }

    public function testIsHandlingLoginFailure(): void
    {
        $this->expectException(VeSyncException::class);
        $this->expectExceptionCode(4031001);
        $this->expectExceptionMessage('account or password wrong');

        $mock = new MockHandler([
            new Response(403, [], '{"error":{"code":4031001,"msg":"account or password wrong"}}'),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $veSync->login('foo', 'bar');
    }

    public function testIsGettingDevices(): void
    {
        /** @var Device[] $expected */
        $expected = [new Device('abc123', 'My Camera', false)];

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                [
                    'deviceName' => 'My Camera',
                    'cid' => 'abc123',
                    'deviceStatus' => 'off',
                ],
            ])),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $token = new Token('foo', 'bar', false);

        $actual = $veSync->getDevices($token);

        $this->assertCount(1, $actual);
        $this->assertEquals($expected, $actual);
    }

    public function testIsHandlingGetDevicesFailure(): void
    {
        $this->expectException(VeSyncException::class);
        $this->expectExceptionCode(4001004);
        $this->expectExceptionMessage('TOKEN expired');

        $token = new Token('123', 'iddqd', false);

        $mock = new MockHandler([
            new Response(403, [], '{"error":{"code":4001004,"msg":"TOKEN expired"}}'),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $veSync->getDevices($token);
    }

    public function testIsTurningOn(): void
    {
        $token = new Token('foo', 'bar', false);
        $device = new Device('abc123', 'My Camera', false);

        $mock = new MockHandler([
            new Response(200),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $actual = $veSync->turnOn($token, $device);

        $this->assertNull($actual);
    }

    public function testIsHandlingTurnOnFailureByBadToken(): void
    {
        $this->expectException(VeSyncException::class);
        $this->expectExceptionCode(4001004);
        $this->expectExceptionMessage('TOKEN过期');

        $token = new Token('foo', 'bar', false);
        $device = new Device('abc123', 'My Camera', true);

        $mock = new MockHandler([
            new Response(403, [], '{"error":{"code":4001004,"msg":"TOKEN过期"}}'),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $veSync->turnOn($token, $device);
    }

    public function testIsHandlingTurnOnFailureByBadDevice(): void
    {
        $this->expectException(VeSyncException::class);
        $this->expectExceptionCode(4041004);
        $this->expectExceptionMessage('device offline');

        $token = new Token('foo', 'bar', false);
        $device = new Device('abc123', 'My Camera', true);

        $mock = new MockHandler([
            new Response(403, [], '{"error":{"code":4041004,"msg":"device offline"}}'),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $veSync->turnOn($token, $device);
    }

    public function testIsTurningOff(): void
    {
        $token = new Token('foo', 'bar', false);
        $device = new Device('abc123', 'My Camera', true);

        $mock = new MockHandler([
            new Response(200),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $actual = $veSync->turnOff($token, $device);

        $this->assertNull($actual);
    }

    public function testIsHandlingTurnOffFailureByBadToken(): void
    {
        $this->expectException(VeSyncException::class);
        $this->expectExceptionCode(4001004);
        $this->expectExceptionMessage('TOKEN过期');

        $token = new Token('foo', 'bar', false);
        $device = new Device('abc123', 'My Camera', true);

        $mock = new MockHandler([
            new Response(403, [], '{"error":{"code":4001004,"msg":"TOKEN过期"}}'),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $veSync->turnOff($token, $device);
    }

    public function testIsHandlingTurnOffFailureByBadDevice(): void
    {
        $this->expectException(VeSyncException::class);
        $this->expectExceptionCode(4041004);
        $this->expectExceptionMessage('device offline');

        $token = new Token('foo', 'bar', false);
        $device = new Device('abc123', 'My Camera', true);

        $mock = new MockHandler([
            new Response(403, [], '{"error":{"code":4041004,"msg":"device offline"}}'),
        ]);

        $client = new Client(['handler' => $mock]);
        $veSync = new VeSync($client);

        $veSync->turnOff($token, $device);
    }
}
