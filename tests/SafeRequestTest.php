<?php declare(strict_types=1);

namespace tests\GW\Safe;

use GW\Safe\SafeAssocArray;
use GW\Safe\SafeRequest;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SafeRequestTest extends TestCase
{
    public function test_mustBeFrom_throws_on_null()
    {
        $this->expectException(LogicException::class);
        SafeRequest::mustBeFrom(null);
    }

    public function test_mustBeFrom_returns_instance()
    {
        $request = new Request();
        self::assertInstanceOf(SafeRequest::class, SafeRequest::mustBeFrom($request));
    }

    public function test_request_returns_original_request()
    {
        $request = new Request();
        self::assertSame($request, SafeRequest::from($request)->request());
    }

    public function test_ip()
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.2.3.4']);
        self::assertSame('1.2.3.4', SafeRequest::from($request)->ip());
    }

    public function test_ip_throws_when_not_set()
    {
        $request = new Request();
        $this->expectException(LogicException::class);
        SafeRequest::from($request)->ip();
    }

    public function test_ipElse_returns_ip_when_set()
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.2.3.4']);
        self::assertSame('1.2.3.4', SafeRequest::from($request)->ipElse('fallback'));
    }

    public function test_ipElse_returns_default_when_not_set()
    {
        $request = new Request();
        self::assertSame('fallback', SafeRequest::from($request)->ipElse('fallback'));
    }

    public function test_ipElse_default_is_unknown()
    {
        $request = new Request();
        self::assertSame('unknown', SafeRequest::from($request)->ipElse());
    }

    public function test_session()
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        self::assertSame($session, SafeRequest::from($request)->session());
    }

    public function test_value()
    {
        $request = new Request(
            ['query_param' => 'from_query', 'shared' => 'from_query'],
            ['post_param' => 'from_post', 'shared' => 'from_post'],
            ['attr_param' => 'from_attr', 'shared' => 'from_attr'],
        );
        $safeRequest = SafeRequest::from($request);

        self::assertEquals('from_query', $safeRequest->value('query_param', null));
        self::assertEquals('from_post', $safeRequest->value('post_param', null));
        self::assertEquals('from_attr', $safeRequest->value('attr_param', null));
        self::assertEquals('from_attr', $safeRequest->value('shared', null), 'attributes take priority');
        self::assertEquals('default', $safeRequest->value('missing', 'default'));
    }

    public function test_value_returns_null_when_key_exists_with_null_value()
    {
        $request = new Request(['param' => null]);
        self::assertNull(SafeRequest::from($request)->value('param', 'default'));
    }

    public function test_from()
    {
        $request = new Request(
            ['query_param' => 1, 'query_array_param' => ['x' => 5]],
            ['request_param' => 'test'],
            ['attribute_param' => ['abc' => 'def']]
        );
        $safeRequest = SafeRequest::from($request);

        self::assertEquals(1, $safeRequest->query()->int('query_param'));
        self::assertEquals(SafeAssocArray::from(['x' => 5]), $safeRequest->query()->array('query_array_param'));
        self::assertEquals(10, $safeRequest->query()->int('not_existent_param', 10));
        self::assertEquals('test', $safeRequest->post()->string('request_param'));
        self::assertEquals(
            SafeAssocArray::from(['abc' => 'def']),
            $safeRequest->attributes()->array('attribute_param')
        );
    }
}
