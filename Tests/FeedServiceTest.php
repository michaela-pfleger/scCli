<?php

namespace Tests;

use Models\SourceDto;
use PHPUnit\Framework\TestCase;
use Models\FeedDaoStub;
use Services\FeedService;
use Models\FeedDto;

class FeedServiceTest extends TestCase
{

    public function testFeedCanBeCopied() {
        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);
        $liveFeedDao->create(new FeedDto(18, 'Michaela Pfleger'));
        $systemUnderTest->copy(18);

        // act
        $feed = $devFeedDao->getFullFeedById(18);

        //assert
        self::assertTrue($feed->getName() === "Michaela Pfleger");
    }

    public function testCopiedFeedCanBeUpdated() {
        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);
        $liveFeedDao->create(new FeedDto(18, 'Michaela Pfleger', [], new SourceDto("@michaela_neu", 0)));
        $systemUnderTest->copy(18);
        $liveFeedDao->create(new FeedDto(18, 'Michaela Pfleger', [], new SourceDto("@michaela_neu", 17)));
        $systemUnderTest->copy(18);

        // act
        $feed = $devFeedDao->getFullFeedById(18);

        //assert
        self::assertTrue($feed->getInstagramSource()->getFanCount() === 17);
    }

    public function testNoValidIdGiven() {
        // assert
        $this->expectException(\InvalidArgumentException::class);

        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);

        // act
        $systemUnderTest->copy(-1);
    }

    public function testInvalidOptionsGiven() {
        // assert
        $this->expectException(\InvalidArgumentException::class);

        //arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);

        // act
        $systemUnderTest->copy(1, true, true);
    }

    public function testInvalidLimitGiven() {
        // assert
        $this->expectException(\InvalidArgumentException::class);

        //arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);

        // act
        $systemUnderTest->copy(1, false, false, -1);
    }

    public function testNothingCopied() {
        //assert
        $this->expectExceptionMessage("Feed not Found");

        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);
        $liveFeedDao->create(new FeedDto(12, 'Michaela Pfleger', [], null, new SourceDto("michaela_tiktok_1", 0)));

        try {
            $systemUnderTest->copy(12, true, true);
        } catch (\InvalidArgumentException $invalidArgumentException) {
            // do nothing
        }

        // act
        $devFeedDao->getFullFeedById(12);

    }

    public function testCopyOnlyInstagram() {
        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);
        $liveFeedDao->create(new FeedDto(12, 'Michaela Pfleger', [], new SourceDto("michaela_instagram_neu", 0), new SourceDto("michaela_tiktok_neu", 0)));

        // act
        $systemUnderTest->copy(12, true);

        $feed = $devFeedDao->getFullFeedById(12);

        // assert
        $this->assertTrue($feed->getInstagramSource() !== null && $feed->getTiktokSource() === null);
    }

    public function testCopyOnlyTiktok() {
        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);
        $liveFeedDao->create(new FeedDto(12, 'Michaela Pfleger', [], new SourceDto("michaela_instagram_neu", 0), new SourceDto("michaela_tiktok_neu", 0)));

        // act
        $systemUnderTest->copy(12, false, true);

        $feed = $devFeedDao->getFullFeedById(12);

        // assert
        $this->assertTrue($feed->getInstagramSource() === null && $feed->getTiktokSource() !== null);
    }

    public function testCopyOnlyOnePost() {
        // arrange
        $liveFeedDao = new FeedDaoStub(true);
        $devFeedDao = new FeedDaoStub();
        $systemUnderTest = new FeedService($liveFeedDao, $devFeedDao);

        // act
        $systemUnderTest->copy(2, true, false, 1);

        $feed = $devFeedDao->getFullFeedById(2);

        // assert
        $this->assertTrue(count($feed->getPosts()) == 1);
    }
}