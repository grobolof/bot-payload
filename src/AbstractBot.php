<?php

declare(strict_types=1);

namespace BotMapperFormatter;

use Symfony\Component\HttpFoundation\Request;
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

abstract readonly class AbstractBot
{
    protected static function serializer(): Serializer
    {
        return new Serializer(
            normalizers: [
                new ArrayDenormalizer(),
                new ObjectNormalizer(
                    classMetadataFactory: new ClassMetadataFactory(loader: new AttributeLoader()),
                    nameConverter: new MetadataAwareNameConverter(
                        metadataFactory: new ClassMetadataFactory(loader: new AttributeLoader()),
                        fallbackNameConverter: new CamelCaseToSnakeCaseNameConverter()
                    ),
                    propertyAccessor: new PropertyAccessor(),
                    propertyTypeExtractor: new ReflectionExtractor()
                )
            ],
            encoders: [new JsonEncoder()]
        );
    }
}
