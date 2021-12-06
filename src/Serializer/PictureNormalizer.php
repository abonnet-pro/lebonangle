<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Picture;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

final class PictureNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'MEDIA_OBJECT_NORMALIZER_ALREADY_CALLED';

    public function __construct(private StorageInterface $storage)
    {
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        if(isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Picture;
    }

    /**
     * @param Picture $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $object->setContentUrl($this->storage->resolveUri($object, 'file'));

        return $this->normalizer->normalize($object, $format, $context);
    }
}