<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Data\RepoData;
use App\Processor\RepoDataProcessor;
use App\Provider\RepoDataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

final class ApiControllerTest extends TestCase
{
    public function testReturnsComparisonAsJson(): void
    {
        $provider = $this->createMock(RepoDataProvider::class);
        $provider->expects(self::exactly(2))->method('getData')->willReturnMap([
            ['a/one', new RepoData('a/one', ['watchers' => 1, 'stars' => 5, 'forks' => 2, 'pullsOpen' => 0, 'pullsClosed' => 0])],
            ['b/two', new RepoData('b/two', ['watchers' => 1, 'stars' => 3, 'forks' => 9, 'pullsOpen' => 0, 'pullsClosed' => 0])],
        ]);

        $controller = new ApiController();
        $controller->setContainer(new Container());

        $response = $controller->index(
            new RepoDataProcessor($provider),
            Request::create('/api', 'GET', ['repo1' => 'a/one', 'repo2' => 'https://github.com/b/two']),
        );

        self::assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame(['a/one', 'b/two'], array_column($data['stats'], 'full_name'));
        self::assertSame([1, -1], array_column($data['diffs'], 'stars'));
        self::assertSame([-1, 1], array_column($data['diffs'], 'forks'));
    }
}
