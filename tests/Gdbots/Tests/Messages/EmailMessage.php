<?php

namespace Gdbots\Tests\Messages;

use Gdbots\Messages\AbstractMessage;
use Gdbots\Messages\Field;
use Gdbots\Messages\FieldBuilder as Fb;
use Gdbots\Messages\Type as T;
use Gdbots\Tests\Messages\Enum\Priority;
use Gdbots\Tests\Messages\Enum\Provider;
use Moontoast\Math\BigNumber;

class EmailMessage extends AbstractMessage
{
    const FROM_NAME  = 'from_name';
    const FROM_EMAIL = 'from_email';
    const SUBJECT    = 'subject';
    const BODY       = 'body';
    const PRIORITY   = 'priority';
    const SENT       = 'sent';
    const PROVIDER   = 'provider';
    const A_BIG_INT  = 'a_big_int';

    /**
     * @return Field[]
     */
    protected static function getFields()
    {
        return [
            Fb::create(self::FROM_NAME,  T\StringType::create())->build(),
            Fb::create(self::FROM_EMAIL, T\StringType::create())
                ->required()
                ->withAssertion(function ($value, Field $field) {
                    \Assert\that($value)->email(null, $field->getName());
                })
                ->build(),
            Fb::create(self::SUBJECT,  T\StringType::create())->build(),
            Fb::create(self::BODY,     T\StringType::create())->build(),
            Fb::create(self::PRIORITY, T\IntEnumType::create())
                ->required()
                ->usingClass('Gdbots\Tests\Messages\Enum\Priority')
                ->withDefault(Priority::NORMAL())
                ->build(),
            Fb::create(self::SENT,     T\BooleanType::create())->build(),
            Fb::create(self::PROVIDER, T\StringEnumType::create())
                ->usingClass('Gdbots\Tests\Messages\Enum\Provider')
                ->withDefault(Provider::GMAIL())
                ->build(),
            Fb::create(self::A_BIG_INT, T\BigIntType::create())->build(),
        ];
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->get(self::FROM_NAME);
    }

    /**
     * @param string $fromName
     * @return self
     */
    public function setFromName($fromName)
    {
        $this->set(self::FROM_NAME, $fromName);
        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->get(self::FROM_EMAIL);
    }

    /**
     * @param string $email
     * @return self
     */
    public function setFromEmail($email)
    {
        $this->set(self::FROM_EMAIL, $email);
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->get(self::SUBJECT);
    }

    /**
     * @param string $subject
     * @return self
     */
    public function setSubject($subject)
    {
        $this->set(self::SUBJECT, $subject);
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->get(self::BODY);
    }

    /**
     * @param string $body
     * @return self
     */
    public function setBody($body)
    {
        $this->set(self::BODY, $body);
        return $this;
    }

    /**
     * @return Priority
     */
    public function getPriority()
    {
        return $this->get(self::PRIORITY);
    }

    /**
     * @param Priority $priority
     * @return self
     */
    public function setPriority(Priority $priority)
    {
        $this->set(self::PRIORITY, $priority);
        return $this;
    }

    /**
     * @return bool
     */
    public function wasSent()
    {
        return $this->get(self::SENT);
    }

    /**
     * @return self
     */
    public function markAsSent()
    {
        $this->set(self::SENT, true);
        return $this;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->get(self::PROVIDER);
    }

    /**
     * @param Provider $provider
     * @return self
     */
    public function setProvider(Provider $provider)
    {
        $this->set(self::PROVIDER, $provider);
        return $this;
    }

    /**
     * @return BigNumber
     */
    public function getABigInt()
    {
        return $this->get(self::A_BIG_INT);
    }

    /**
     * @param BigNumber $aBigInt
     * @return self
     */
    public function setABigInt(BigNumber $aBigInt)
    {
        $this->set(self::A_BIG_INT, $aBigInt);
        return $this;
    }
}