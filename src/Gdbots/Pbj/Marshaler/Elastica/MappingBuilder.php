<?php

namespace Gdbots\Pbj\Marshaler\Elastica;

use Elastica\Type\Mapping;
use Gdbots\Common\Util\StringUtils;
use Gdbots\Pbj\Enum\Format;
use Gdbots\Pbj\Field;
use Gdbots\Pbj\Message;
use Gdbots\Pbj\Schema;

class MappingBuilder
{
    /**
     * Map of pbj type -> elastica mapping types.
     * @var array
     */
    protected $types = [
        'big-int'           => ['type' => 'long'],
        'binary'            => ['type' => 'binary'],
        'blob'              => ['type' => 'binary'],
        'boolean'           => ['type' => 'boolean'],
        'date'              => ['type' => 'dateOptionalTime'],
        'date-time'         => ['type' => 'dateOptionalTime'],
        'decimal'           => ['type' => 'double'],
        'float'             => ['type' => 'float'],
        'geo-point'         => ['type' => 'geo_point'],
        'identifier'        => ['type' => 'string', 'index' => 'not_analyzed'],
        'int'               => ['type' => 'long'],
        'int-enum'          => ['type' => 'integer'],
        'medium-blob'       => ['type' => 'binary'],
        'medium-int'        => ['type' => 'integer'],
        'medium-text'       => ['type' => 'string'],
        'message'           => ['type' => 'nested'],
        'message-ref'       => [
            'type' => 'object',
            'properties' => [
                    'curie' => ['type' => 'string', 'index' => 'not_analyzed'],
                    'id'    => ['type' => 'string', 'index' => 'not_analyzed'],
                    'tag'   => ['type' => 'string', 'index' => 'not_analyzed'],
            ]
        ],
        'microtime'         => ['type' => 'long'],
        'signed-big-int'    => ['type' => 'long'],
        'signed-int'        => ['type' => 'integer'],
        'signed-medium-int' => ['type' => 'long'],
        'signed-small-int'  => ['type' => 'short'],
        'signed-tiny-int'   => ['type' => 'byte'],
        'small-int'         => ['type' => 'integer'],
        'string'            => ['type' => 'string'],
        'string-enum'       => ['type' => 'string', 'index' => 'not_analyzed'],
        'text'              => ['type' => 'string'],
        'time-uuid'         => ['type' => 'string', 'index' => 'not_analyzed'],
        'timestamp'         => ['type' => 'dateOptionalTime'],
        'tiny-int'          => ['type' => 'short'],
        'uuid'              => ['type' => 'string', 'index' => 'not_analyzed'],
    ];

    /**
     * @param Schema $schema
     * @return Mapping
     */
    public function build(Schema $schema)
    {
        return new Mapping(null, $this->mapSchema($schema));
    }

    /**
     * @param Schema $schema
     * @param bool $root
     * @return array
     */
    protected function mapSchema(Schema $schema, $root = true)
    {
        $map = [];

        foreach ($schema->getFields() as $field) {
            $fieldName = $field->getName();
            $type = $field->getType();

            if ($fieldName === Schema::PBJ_FIELD_NAME) {
                $map[$fieldName] = ['type' => 'string', 'index' => 'not_analyzed'];
                continue;
            }

            $method = 'map' . ucfirst(StringUtils::toCamelFromSlug($type->getTypeValue()));

            if (is_callable([$this, $method])) {
                $map[$fieldName] = $this->$method($field, $root);
            } else {
                $map[$fieldName] = $this->types[$type->getTypeValue()];
            }
        }

        return $map;
    }

    /**
     * todo: review, should be default include_in_parent to true?
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/1.4/mapping-nested-type.html
     *
     * @param Field $field
     * @return array
     */
    protected function mapMessage(Field $field)
    {
        $type = $field->getType();

        /** @var Message $class */
        $class = $field->getClassName();
        if (class_exists($class)) {
            $schema = $class::schema();
            return ['type' => 'nested', 'properties' => $this->mapSchema($schema)];
        }

        return $this->types[$type->getTypeValue()];
    }

    /**
     * todo: autoset most likely/useful analyzers?
     *
     * @param Field $field
     * @return array
     */
    protected function mapString(Field $field)
    {
        switch ($field->getFormat()->getValue()) {
            case Format::DATE:
            case Format::DATE_TIME:
                return $this->types['date-time'];

            case Format::DATED_SLUG:
            case Format::SLUG:
            case Format::EMAIL:
            case Format::HASHTAG:
            case Format::HOSTNAME:
            case Format::IPV6:
            case Format::UUID:
            case Format::URI:
            case Format::URL:
                return ['type' => 'string', 'index' => 'not_analyzed'];

            case Format::IPV4:
                return ['type' => 'ip'];

            default:
                return ['type' => 'string'];
        }
    }
}