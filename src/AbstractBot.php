<?php

declare(strict_types=1);

namespace BotMapperFormatter;

use BotMapperFormatter\Exception\InvalidPayloadException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractBot
{
    private static ?Serializer $serializer = null;

    protected static function serializer(): Serializer
    {
        if (self::$serializer instanceof Serializer) {
            return self::$serializer;
        }

        $classMetadataFactory = new ClassMetadataFactory(loader: new AttributeLoader());

        self::$serializer = new Serializer(
            normalizers: [
                new ArrayDenormalizer(),
                new ObjectNormalizer(
                    classMetadataFactory: $classMetadataFactory,
                    nameConverter: new MetadataAwareNameConverter(
                        metadataFactory: $classMetadataFactory,
                        fallbackNameConverter: new CamelCaseToSnakeCaseNameConverter()
                    ),
                    propertyAccessor: new PropertyAccessor(),
                    propertyTypeExtractor: new ReflectionExtractor()
                ),
            ],
            encoders: [new JsonEncoder()]
        );

        return self::$serializer;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function decode(array|string $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        try {
            $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidPayloadException(
                message: 'Некорректный JSON: ' . $exception->getMessage(),
                previous: $exception
            );
        }

        if (!is_array($decoded)) {
            throw new InvalidPayloadException('Payload должен быть JSON-объектом.');
        }

        return $decoded;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @param array<string, mixed> $data
     * @return T
     */
    protected static function hydrate(array $data, string $type): object
    {
        try {
            /** @var T $model */
            $model = self::serializer()->denormalize(data: $data, type: $type);

            return $model;
        } catch (\Throwable $exception) {
            throw new InvalidPayloadException(
                message: sprintf('Не удалось гидратировать %s: %s', $type, $exception->getMessage()),
                previous: $exception
            );
        }
    }
}
