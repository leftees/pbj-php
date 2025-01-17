<?php

namespace Gdbots\Pbj;

use Gdbots\Pbj\Exception\MoreThanOneMessageForMixin;
use Gdbots\Pbj\Exception\NoMessageForCurie;
use Gdbots\Pbj\Exception\NoMessageForMixin;
use Gdbots\Pbj\Exception\NoMessageForSchemaId;

final class MessageResolver
{
    /**
     * An array of all the available class names keyed by the schema resolver key
     * and curies for resolution that is not version specific.
     *
     * @var array
     */
    private static $messages = [];

    /**
     * An array of resolved messages in this request.
     * @var array
     */
    private static $resolved = [];

    /**
     * An array of resolved lookups by mixin, keyed by the mixin id with major rev
     * and optionally a package and category (for faster lookups)
     * @see SchemaId::getCurieWithMajorRev
     *
     * @var Schema[]
     */
    private static $resolvedMixins = [];

    /**
     * Returns the fully qualified php class name to be used for the provided schema id.
     *
     * @param SchemaId $schemaId
     * @return string
     * @throws NoMessageForSchemaId
     */
    public static function resolveSchemaId(SchemaId $schemaId)
    {
        $curieMajor = $schemaId->getCurieWithMajorRev();
        if (isset(self::$resolved[$curieMajor])) {
            return self::$resolved[$curieMajor];
        }

        if (isset(self::$messages[$curieMajor])) {
            $className = self::$messages[$curieMajor];
            self::$resolved[$curieMajor] = $className;
            return $className;
        }

        $curie = $schemaId->getCurie()->toString();
        if (isset(self::$messages[$curie])) {
            $className = self::$messages[$curie];
            self::$resolved[$curieMajor] = $className;
            self::$resolved[$curie] = $className;
            return $className;
        }

        throw new NoMessageForSchemaId($schemaId);
    }

    /**
     * Returns the fully qualified php class name to be used for the provided curie.
     *
     * @param MessageCurie $curie
     * @return string
     * @throws NoMessageForCurie
     */
    public static function resolveMessageCurie(MessageCurie $curie)
    {
        $key = $curie->toString();
        if (isset(self::$resolved[$key])) {
            return self::$resolved[$key];
        }

        if (isset(self::$messages[$key])) {
            $className = self::$messages[$key];
            self::$resolved[$key] = $className;
            return $className;
        }

        throw new NoMessageForCurie($curie);
    }

    /**
     * Adds a single schema to the resolver.  This is used in tests or dynamic
     * message schema creation (not a typical use case).
     *
     * @param Schema $schema
     */
    public static function registerSchema(Schema $schema)
    {
        self::$messages[$schema->getId()->getCurieWithMajorRev()] = $schema->getClassName();
    }

    /**
     * Adds a single schema id and class name.
     * @see SchemaId::getCurieWithMajorRev
     *
     * @param SchemaId|string $id
     * @param string $className
     */
    public static function register($id, $className)
    {
        if ($id instanceof SchemaId) {
            $id = $id->getCurieWithMajorRev();
        }
        self::$messages[(string) $id] = $className;
    }

    /**
     * Registers an array of id => className values to the resolver.
     *
     * @param array $map
     */
    public static function registerMap(array $map)
    {
        if (empty(self::$messages)) {
            self::$messages = $map;
            return;
        }
        self::$messages = array_merge(self::$messages, $map);
    }

    /**
     * Return the one schema expected to be using the provided mixin.
     *
     * @param Mixin $mixin
     * @param string $inPackage
     * @param string $inCategory
     * @return Schema
     *
     * @throws MoreThanOneMessageForMixin
     * @throws NoMessageForMixin
     */
    public static function findOneUsingMixin(Mixin $mixin, $inPackage = null, $inCategory = null)
    {
        $schemas = self::findAllUsingMixin($mixin, $inPackage, $inCategory);
        if (1 !== count($schemas)) {
            throw new MoreThanOneMessageForMixin($mixin, $schemas);
        }

        return current($schemas);
    }

    /**
     * Returns an array of Schemas expected to be using the provided mixin.
     * todo: write unit tests for MessageResolver
     *
     * @param Mixin $mixin
     * @param string $inPackage
     * @param string $inCategory
     * @return Schema[]
     *
     * @throws NoMessageForMixin
     */
    public static function findAllUsingMixin(Mixin $mixin, $inPackage = null, $inCategory = null)
    {
        $mixinId = $mixin->getId()->getCurieWithMajorRev();
        $key = sprintf('%s%s%s', $mixinId, $inPackage, $inCategory);

        if (!isset(self::$resolvedMixins[$key])) {
            $filtered = !empty($inPackage) || !empty($inCategory);
            /** @var Message $class */
            $schemas = [];
            foreach (self::$messages as $id => $class) {
                if ($filtered) {
                    list($vendor, $package, $category, $message) = explode(':', $id);
                    if (!empty($inPackage) && $package != $inPackage) {
                        continue;
                    }
                    if (!empty($inCategory) && $category != $inCategory) {
                        continue;
                    }
                }

                $schema = $class::schema();
                if ($schema->hasMixin($mixinId)) {
                    $schemas[] = $schema;
                }
            }
            self::$resolvedMixins[$key] = $schemas;
        } else {
            $schemas = self::$resolvedMixins[$key];
        }

        if (empty($schemas)) {
            throw new NoMessageForMixin($mixin);
        }

        return $schemas;
    }
}
