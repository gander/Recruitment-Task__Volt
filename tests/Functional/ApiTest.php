<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ApiTest extends WebTestCase
{
    public function testComparesTwoRepositories(): void
    {
        $client = static::createClient();
        static::getContainer()->set('github.client', new MockHttpClient(self::github([
            'a/one' => [['full_name' => 'a/one', 'subscribers_count' => 1, 'stargazers_count' => 5, 'forks_count' => 2], [['state' => 'open'], ['state' => 'closed']]],
            'b/two' => [['full_name' => 'b/two', 'subscribers_count' => 1, 'stargazers_count' => 3, 'forks_count' => 9], []],
        ])));

        $client->request('GET', '/api?repo1=a/one&repo2=https://github.com/b/two');

        self::assertResponseStatusCodeSame(200);
        $data = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(['a/one', 'b/two'], array_column($data['stats'], 'full_name'));
        self::assertSame([5, 3], array_column($data['stats'], 'stars'));
        self::assertSame([1, -1], array_column($data['diffs'], 'stars'));
        self::assertSame([-1, 1], array_column($data['diffs'], 'forks'));
        self::assertSame(1, $data['stats'][0]['pulls_open']);
        self::assertSame(1, $data['stats'][0]['pulls_closed']);
    }

    #[DataProvider('invalidRequests')]
    public function testInvalidRepositoryNameReturns400(string $query, string $parameter): void
    {
        $client = static::createClient();

        $client->request('GET', '/api?' . $query);

        self::assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($parameter, $data['parameter']);
        self::assertSame('Invalid repository url', $data['error']);
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidRequests(): iterable
    {
        yield 'first malformed' => ['repo1=bad&repo2=laravel/laravel', 'repo1'];
        yield 'second malformed' => ['repo1=symfony/symfony&repo2=a/b/c', 'repo2'];
        yield 'missing parameters' => ['', 'repo1'];
    }

    /**
     * @param array<string, array{array<string, mixed>, list<array<string, string>>}> $repositories
     */
    private static function github(array $repositories): callable
    {
        return static function (string $method, string $url) use ($repositories): MockResponse {
            foreach ($repositories as $fullName => [$repository, $pulls]) {
                if ($url === "https://api.github.com/repos/{$fullName}") {
                    return new MockResponse(json_encode($repository), ['response_headers' => ['content-type: application/json']]);
                }
                if ($url === "https://api.github.com/repos/{$fullName}/pulls") {
                    return new MockResponse(json_encode($pulls), ['response_headers' => ['content-type: application/json']]);
                }
            }

            return new MockResponse('{}', ['http_code' => 404]);
        };
    }
}
