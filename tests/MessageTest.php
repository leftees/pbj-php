<?php

namespace Gdbots\Tests\Pbj;

use Gdbots\Common\Enum;
use Gdbots\Pbj\Exception\FrozenMessageIsImmutable;
use Gdbots\Tests\Pbj\Fixtures\Enum\Priority;
use Gdbots\Tests\Pbj\Fixtures\EmailMessage;
use Gdbots\Tests\Pbj\Fixtures\Enum\Provider;
use Gdbots\Tests\Pbj\Fixtures\MapsMessage;
use Gdbots\Tests\Pbj\Fixtures\NestedMessage;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    use FixtureLoader;

    public function testCreateMessageFromArray()
    {
        /** @var EmailMessage $message */
        $message = $this->createEmailMessage();
        $message->setPriority(Priority::HIGH());

        $this->assertTrue($message->getPriority()->equals(Priority::HIGH));
        $this->assertTrue(Priority::HIGH() === $message->getPriority());

        $json = $this->getSerializer()->serialize($message);
        $message = $this->getSerializer()->deserialize($json);

        $this->assertTrue($message->getPriority()->equals(Priority::HIGH));
        $this->assertTrue(Priority::HIGH() === $message->getPriority());
        $this->assertSame($message->getNested()->getLocation()->getLatitude(), 0.5);

        //echo json_encode($message, JSON_PRETTY_PRINT);
        //echo json_encode($message->schema(), JSON_PRETTY_PRINT);
        //echo json_encode($message->schema()->getMixins(), JSON_PRETTY_PRINT);
    }

    public function testUniqueItemsInSet()
    {
        $message = EmailMessage::create()
            ->addLabel('CHICKEN')
            ->addLabel('Chicken')
            ->addLabel('chicken')
            ->addLabel('DONUTS')
            ->addLabel('Donuts')
            ->addLabel('donuts')
        ;

        $this->assertCount(2, $message->getLabels());
        $this->assertSame($message->getLabels(), ['chicken', 'donuts']);
    }

    public function testisInSet()
    {
        $message = EmailMessage::create()
            ->addLabel('abc')
            ->addToSet(
                'enum_in_set',
                [
                    Provider::AOL(),
                    Provider::GMAIL(),
                ]
            );

        $this->assertTrue($message->isInSet('labels', 'abc'));
        $this->assertFalse($message->isInSet('labels', 'idontexist'));
        $this->assertTrue($message->isInSet('enum_in_set', Provider::AOL()));
        $this->assertFalse($message->isInSet('enum_in_set', Provider::HOTMAIL()));
    }

    public function testEnumInSet()
    {
        $message = EmailMessage::create()
            ->addToSet(
                'enum_in_set',
                [
                    Provider::AOL(),
                    Provider::AOL(),
                    Provider::GMAIL(),
                    Provider::GMAIL(),
                ]
            );

        $result = array_map(
            function (Enum $enum) {
                return $enum->getValue();
            },
            $message->get('enum_in_set') ?: []
        );

        $this->assertCount(2, $result);
        $this->assertSame($result, ['aol', 'gmail']);
    }

    public function testisInList()
    {
        $message = $this->createEmailMessage();

        /** @var MapsMessage $messageInList */
        $messageInList = $message->get('any_of_message')[0];
        $messageNotInList = clone $messageInList;
        $messageNotInList->addToAMap('String', 'key', 'val');

        $this->assertTrue($message->isInList('any_of_message', $messageInList));
        $this->assertFalse($message->isInList('any_of_message', $messageNotInList));
        $this->assertFalse($message->isInList('any_of_message', 'notinlist'));
        $this->assertFalse($message->isInList('any_of_message', NestedMessage::create()));
        $this->assertTrue($message->isInList('enum_in_list', 'aol'));
        $this->assertTrue($message->isInList('enum_in_list', Provider::AOL()));
        $this->assertFalse($message->isInList('enum_in_list', 'notinlist'));
        $this->assertFalse($message->isInList('enum_in_list', Provider::HOTMAIL()));
    }

    public function testEnumInList()
    {
        $message = EmailMessage::create()
            ->addToList(
                'enum_in_list',
                [
                    Provider::AOL(),
                    Provider::AOL(),
                    Provider::GMAIL(),
                    Provider::GMAIL(),
                ]
            );

        $result = array_map(
            function (Enum $enum) {
                return $enum->getValue();
            },
            $message->get('enum_in_list')
        );

        $this->assertCount(4, $result);
        $this->assertSame($result, ['aol', 'aol', 'gmail', 'gmail']);
    }

    public function testisInMap()
    {
        $message = MapsMessage::create();
        $message->addToAMap('String', 'string1', 'val1');

        $this->assertTrue($message->isInMap('String', 'string1'));
        $this->assertFalse($message->isInMap('String', 'notinmap'));
        $this->assertFalse($message->isInMap('Microtime', 'notinmap'));

        $message->clear('String');
        $this->assertFalse($message->isInMap('String', 'string1'));
    }

    public function testNestedMessage()
    {
        $message = $this->createEmailMessage();
        $nestedMessage = NestedMessage::create()
            ->setTest1('val1')
            ->addTest2(1)
            ->addTest2(2)
        ;

        $message->setNested($nestedMessage);
        $this->assertSame($nestedMessage->getTest2(), [1, 2]);
        $this->assertSame($message->getNested(), $nestedMessage);
    }

    public function testAnyOfMessageInList()
    {
        $message = EmailMessage::create()
            ->addToList(
                'any_of_message',
                [
                    MapsMessage::create()->addToMap('String', 'test:field:name', 'value1'),
                    NestedMessage::create()->setTest1('value1')
                ]
        );

        $this->assertCount(2, $message->get('any_of_message'));
    }

    public function testFreeze()
    {
        $message = $this->createEmailMessage();
        $nestedMessage = NestedMessage::create();
        $message->setNested($nestedMessage);

        $message->freeze();
        $this->assertTrue($message->isFrozen());
        $this->assertTrue($nestedMessage->isFrozen());
    }

    /**
     * @expectedException \Gdbots\Pbj\Exception\FrozenMessageIsImmutable
     */
    public function testFrozenMessageIsImmutable()
    {
        $message = $this->createEmailMessage();
        $nestedMessage = NestedMessage::create();
        $message->setNested($nestedMessage);

        $message->freeze();
        $message->setFromName('homer');
        $nestedMessage->setTest1('test1');
    }

    public function testClone()
    {
        $message = $this->createEmailMessage();
        $nestedMessage = NestedMessage::create();
        $message->setNested($nestedMessage);

        $nestedMessage->setTest1('original');
        $message2 = clone $message;
        $message2->setFromName('marge')->getNested()->setTest1('clone');

        $this->assertNotSame($message, $message2);
        $this->assertNotSame($message->getDateSent(), $message2->getDateSent());
        $this->assertNotSame($message->getMicrotimeSent(), $message2->getMicrotimeSent());
        $this->assertNotSame($message->getNested(), $message2->getNested());
        $this->assertNotSame($message->getNested()->getTest1(), $message2->getNested()->getTest1());
    }

    public function testCloneIsMutableAfterOriginalIsFrozen()
    {
        $message = $this->createEmailMessage();
        $nestedMessage = NestedMessage::create();
        $message->setNested($nestedMessage);

        $nestedMessage->setTest1('original');
        $message->freeze();

        $message2 = clone $message;
        $message2->setFromName('marge')->getNested()->setTest1('clone');

        try {
            $message->setFromName('homer')->getNested()->setTest1('original');
            $this->fail('Original message should still be immutable.');
        } catch (FrozenMessageIsImmutable $e) {
        }
    }
}
