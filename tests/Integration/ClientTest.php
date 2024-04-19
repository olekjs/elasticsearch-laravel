<?php

namespace Olekjs\Elasticsearch\Tests\Integration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Olekjs\Elasticsearch\Bulk\Bulk;
use Olekjs\Elasticsearch\Client;
use Olekjs\Elasticsearch\Dto\BulkResponseDto;
use Olekjs\Elasticsearch\Dto\IndexResponseDto;
use Olekjs\Elasticsearch\Dto\PaginateResponseDto;
use Olekjs\Elasticsearch\Dto\SearchResponseDto;
use Olekjs\Elasticsearch\Exceptions\ConflictResponseException;
use Olekjs\Elasticsearch\Exceptions\DeleteResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexNotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\IndexResponseException;
use Olekjs\Elasticsearch\Exceptions\NotFoundResponseException;
use Olekjs\Elasticsearch\Exceptions\SearchResponseException;
use Olekjs\Elasticsearch\Exceptions\UpdateResponseException;
use Olekjs\Elasticsearch\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ClientTest extends TestCase
{
    public function testSearchMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/search_success_response.json')
            );
        });

        $client = new Client();

        $result = $client->search('hello', [
            'query' => [
                'match_all' => (object)[]
            ]
        ]);

        $this->assertSame(0, $result->getTook());

        $this->assertSame(1, $result->getShards()->getTotal());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(0, $result->getShards()->getSkipped());
        $this->assertSame(1, $result->getShards()->getSuccessful());

        $this->assertFalse($result->getIsTimedOut());

        $this->assertSame(['value' => 5, 'relation' => 'eq'], $result->getResult()->getTotal());
        $this->assertSame(1.0, $result->getResult()->getMaxScore());

        foreach ($result->getResult()->getHits() as $hit) {
            $this->assertSame('hello', $hit->getIndex());
            $this->assertSame('183865906814918156', $hit->getId());
            $this->assertSame(1.0, $hit->getScore());
            $this->assertSame(['hello' => 'world'], $hit->getSource());
        }
    }

    public function testWrongIndexNameInSearchMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/search_wrong_index_name_exception_response.json'),
            true
        );

        $this->expectException(SearchResponseException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(
            data_get($data, 'error.reason')
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_BAD_REQUEST
            );
        });

        $client = new Client();

        $client->search('hello', [
            'query' => [
                'match_all' => (object)[]
            ]
        ]);
    }

    public function testFindMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/find_success_response.json')
            );
        });

        $client = new Client();

        $result = $client->find('hello', 123);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('183865906814918156', $result->getId());
        $this->assertSame(1, $result->getVersion());
        $this->assertSame(5, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
        $this->assertTrue($result->isFound());
        $this->assertSame(['hello' => 'world'], $result->getSource());
    }

    public function testNotFoundIsNullResponseInFindMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/find_not_found_response.json'),
                Response::HTTP_NOT_FOUND
            );
        });

        $client = new Client();

        $result = $client->find('hello', 123);

        $this->assertNull($result);
    }

    public function testFindOrFailMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/find_success_response.json')
            );
        });

        $client = new Client();

        $result = $client->findOrFail('hello', 123);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('183865906814918156', $result->getId());
        $this->assertSame(1, $result->getVersion());
        $this->assertSame(5, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
        $this->assertTrue($result->isFound());
        $this->assertSame(['hello' => 'world'], $result->getSource());
    }

    public function testNotFoundExceptionInFindOrFailMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/find_not_found_response.json'),
            true
        );

        $id = 123;
        $index = 'hello';

        $this->expectException(NotFoundResponseException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(
            "Document [$id] in index [$index] not found."
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_NOT_FOUND
            );
        });

        $client = new Client();

        $client->findOrFail($index, $id);
    }

    public function testWrongIndexNameExceptionInFindMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/find_wrong_index_name_exception_response.json'),
            true
        );

        $this->expectException(IndexNotFoundResponseException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(
            data_get($data, 'error.reason')
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_NOT_FOUND
            );
        });

        $client = new Client();

        $client->find('hello', 123);
    }

    public function testCreateMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/create_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->create('hello', 123, ['test' => 'hello']);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('123', $result->getId());
        $this->assertSame(1, $result->getVersion());
        $this->assertSame('created', $result->getResult());

        $this->assertSame(2, $result->getShards()->getTotal());
        $this->assertSame(1, $result->getShards()->getSuccessful());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(null, $result->getShards()->getSkipped());

        $this->assertSame(23, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
    }

    public function testMapperParserExceptionInCreateMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/create_mapper_parser_exception_response.json'),
            true
        );

        $this->expectException(IndexResponseException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(
            json_encode($data)
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_BAD_REQUEST
            );
        });

        $client = new Client();

        $client->create('hello', 123, ['hello' => 'world']);
    }

    public function testUpdateMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/update_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->update('hello', 123, ['test' => 'hello']);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('123', $result->getId());
        $this->assertSame(2, $result->getVersion());
        $this->assertSame('updated', $result->getResult());

        $this->assertSame(2, $result->getShards()->getTotal());
        $this->assertSame(1, $result->getShards()->getSuccessful());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(null, $result->getShards()->getSkipped());

        $this->assertSame(24, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
    }

    public function testDocumentMissingExceptionInUpdateMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/update_document_missing_exception_response.json'),
            true
        );

        $this->expectException(NotFoundResponseException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(
            json_encode($data)
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_NOT_FOUND
            );
        });

        $client = new Client();

        $client->update('hello', 123, ['hello' => 'world']);
    }

    public function testMapperParsingExceptionInUpdateMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/update_mapper_parsing_exception_response.json'),
            true
        );

        $this->expectException(UpdateResponseException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(
            json_encode($data)
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_BAD_REQUEST
            );
        });

        $client = new Client();

        $client->update('hello', 123, ['hello' => 'world']);
    }

    public function testVersionConflictEngineExceptionInUpdateMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/update_version_conflict_engine_exception_response.json'),
            true
        );

        $this->expectException(ConflictResponseException::class);
        $this->expectExceptionCode(Response::HTTP_CONFLICT);
        $this->expectExceptionMessage(
            json_encode($data)
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_CONFLICT
            );
        });

        $client = new Client();

        $client->update('hello', 123, ['hello' => 'world'], [], 1, 2);
    }

    public function testDeleteMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/delete_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->delete('hello', 123);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('123', $result->getId());
        $this->assertSame(4, $result->getVersion());
        $this->assertSame('deleted', $result->getResult());

        $this->assertSame(2, $result->getShards()->getTotal());
        $this->assertSame(1, $result->getShards()->getSuccessful());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(null, $result->getShards()->getSkipped());

        $this->assertSame(27, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
    }

    public function testNotFoundExceptionInDeleteMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/delete_not_found_exception_response.json'),
            true
        );

        $this->expectException(NotFoundResponseException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(
            json_encode($data)
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_NOT_FOUND
            );
        });

        $client = new Client();

        $client->delete('hello', 123);
    }

    public function testIndexNotFoundExceptionInDeleteMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/delete_index_not_found_exception_response.json'),
            true
        );

        $this->expectException(DeleteResponseException::class);
        $this->expectExceptionCode(Response::HTTP_NOT_FOUND);
        $this->expectExceptionMessage(
            json_encode($data)
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_NOT_FOUND
            );
        });

        $client = new Client();

        $client->delete('hello', 123);
    }

    public function testEmptyResponseInSearchMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/search_empty_response.json')
            );
        });

        $client = new Client();

        $result = $client->search('hello', [
            'query' => [
                'match_all' => (object)[]
            ]
        ]);

        $this->assertSame(0, $result->getTook());

        $this->assertSame(1, $result->getShards()->getTotal());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(0, $result->getShards()->getSkipped());
        $this->assertSame(1, $result->getShards()->getSuccessful());

        $this->assertFalse($result->getIsTimedOut());

        $this->assertSame(['value' => 0, 'relation' => 'eq'], $result->getResult()->getTotal());
        $this->assertNull($result->getResult()->getMaxScore());
        $this->assertEmpty($result->getResult()->getHits());
    }

    public function testSearchWhereInMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/search_where_in_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->searchWhereIn('hello', 'ids', [1, 2, 3]);

        $this->assertSame(0, $result->getTook());

        $this->assertSame(1, $result->getShards()->getTotal());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(0, $result->getShards()->getSkipped());
        $this->assertSame(1, $result->getShards()->getSuccessful());

        $this->assertFalse($result->getIsTimedOut());

        $this->assertSame(['value' => 2, 'relation' => 'eq'], $result->getResult()->getTotal());
        $this->assertSame(1.0, $result->getResult()->getMaxScore());

        foreach ($result->getResult()->getHits() as $hit) {
            $this->assertSame('hello', $hit->getIndex());
            $this->assertSame('183865906814918156', $hit->getId());
            $this->assertSame(1.0, $hit->getScore());
            $this->assertSame(['hello' => 'world'], $hit->getSource());
        }
    }

    public function testSearchWhereKeywordMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/search_where_keyword_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->searchWhereKeyword('hello', 'name', 'hello');

        $this->assertSame(0, $result->getTook());

        $this->assertSame(1, $result->getShards()->getTotal());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(0, $result->getShards()->getSkipped());
        $this->assertSame(1, $result->getShards()->getSuccessful());

        $this->assertFalse($result->getIsTimedOut());

        $this->assertSame(['value' => 1, 'relation' => 'eq'], $result->getResult()->getTotal());
        $this->assertSame(1.6739764, $result->getResult()->getMaxScore());

        foreach ($result->getResult()->getHits() as $hit) {
            $this->assertSame('hello', $hit->getIndex());
            $this->assertSame('183865906814918156', $hit->getId());
            $this->assertSame(1.6739764, $hit->getScore());
            $this->assertSame(['hello' => 'world'], $hit->getSource());
        }
    }

    public function testSearchWhereLikeMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/search_where_like_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->searchWhereLike('hello', 'name', 'hello');

        $this->assertSame(0, $result->getTook());

        $this->assertSame(1, $result->getShards()->getTotal());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(0, $result->getShards()->getSkipped());
        $this->assertSame(1, $result->getShards()->getSuccessful());

        $this->assertFalse($result->getIsTimedOut());

        $this->assertSame(['value' => 1, 'relation' => 'eq'], $result->getResult()->getTotal());
        $this->assertSame(1.0, $result->getResult()->getMaxScore());

        foreach ($result->getResult()->getHits() as $hit) {
            $this->assertSame('hello', $hit->getIndex());
            $this->assertSame('183865906814918156', $hit->getId());
            $this->assertSame(1.0, $hit->getScore());
            $this->assertSame(['hello' => 'world'], $hit->getSource());
        }
    }

    public function testIncrementMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/increment_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->update('hello', 123, ['test' => 'hello']);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('567890', $result->getId());
        $this->assertSame(5, $result->getVersion());
        $this->assertSame('updated', $result->getResult());

        $this->assertSame(2, $result->getShards()->getTotal());
        $this->assertSame(1, $result->getShards()->getSuccessful());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(null, $result->getShards()->getSkipped());

        $this->assertSame(4, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
    }

    public function testDecrementMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/decrement_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->update('hello', 123, ['test' => 'hello']);

        $this->assertSame('hello', $result->getIndex());
        $this->assertSame('567890', $result->getId());
        $this->assertSame(5, $result->getVersion());
        $this->assertSame('updated', $result->getResult());

        $this->assertSame(2, $result->getShards()->getTotal());
        $this->assertSame(1, $result->getShards()->getSuccessful());
        $this->assertSame(0, $result->getShards()->getFailed());
        $this->assertSame(null, $result->getShards()->getSkipped());

        $this->assertSame(4, $result->getSequenceNumber());
        $this->assertSame(1, $result->getPrimaryTerm());
    }

    public function testCountMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/count_successful_response.json')
            );
        });

        $client = new Client();

        $result = $client->count('test');

        $this->assertSame(11, $result);
    }

    public function testExceptionInCountMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/count_exception_response.json'),
            true
        );

        $this->expectException(SearchResponseException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);

        $this->expectExceptionMessage(
            data_get($data, 'error.reason')
        );

        Http::fake(function () use ($data) {
            return Http::response($data, Response::HTTP_BAD_REQUEST);
        });

        $client = new Client();

        $client->count('test');
    }

    public function testPaginateMethod(): void
    {
        Http::fakeSequence()
            ->push(file_get_contents('tests/Responses/count_successful_response.json'))
            ->push(file_get_contents('tests/Responses/paginate_successful_response.json'));

        $client = new Client();

        $result = $client->paginate('test', [], 2, 100);

        $this->assertInstanceOf(PaginateResponseDto::class, $result);

        $this->assertSame(100, $result->getPerPage());
        $this->assertSame(2, $result->getCurrentPage());
        $this->assertSame(11, $result->getTotalDocuments());
        $this->assertSame(1, $result->getTotalPages());

        $this->assertInstanceOf(SearchResponseDto::class, $result->getDocuments());
    }

    public function testSearchResponseCanBeConvertedToCollection(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/search_success_response.json')
            );
        });

        $client = new Client();

        $result = $client->search('hello', [
            'query' => [
                'match_all' => (object)[]
            ]
        ]);

        $this->assertInstanceOf(Collection::class, $result->toCollect());

        foreach ($result->toCollect() as $source) {
            $this->assertSame(['hello' => 'world'], $source);
        }
    }

    public function testSuccessfulBulkMethod(): void
    {
        Http::fake(function () {
            return Http::response(
                file_get_contents('tests/Responses/bulk_success_response.json')
            );
        });

        $client = new Client();
        $bulk = new Bulk();

        $bulk->add(action: 'index', index: 'products', id: 1, data: ['name' => 'test']);

        $result = $client->bulk($bulk);

        $this->assertInstanceOf(BulkResponseDto::class, $result);
        $this->assertSame(2, $result->getTook());
        $this->assertFalse($result->isErrors());

        foreach ($result->getItems() as $item) {
            $this->assertContains($item->getAction(), ['index', 'update', 'delete']);
            $this->assertInstanceOf(IndexResponseDto::class, $item->getData());
        }
    }

    public function testExceptionInBulkMethod(): void
    {
        $data = json_decode(
            file_get_contents('tests/Responses/bulk_exception_response.json'),
            true
        );

        $this->expectException(SearchResponseException::class);
        $this->expectExceptionCode(Response::HTTP_BAD_REQUEST);
        $this->expectExceptionMessage(
            data_get($data, 'error.reason')
        );

        Http::fake(function () use ($data) {
            return Http::response(
                $data,
                Response::HTTP_BAD_REQUEST
            );
        });

        $client = new Client();
        $bulk = new Bulk();

        $bulk->add(action: 'index', index: 'products', id: 1, data: ['name' => 'test']);

        $client->bulk($bulk);
    }
}
