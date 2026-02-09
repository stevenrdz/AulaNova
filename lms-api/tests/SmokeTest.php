<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testHealthEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');
        $this->assertResponseIsSuccessful();
    }
}
