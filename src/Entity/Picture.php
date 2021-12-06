<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreatePictureAction;
use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: PictureRepository::class)]
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            'controller' => CreatePictureAction::class,
            'deserialize' => false,
            'validation_groups' => ['Default', 'media_object_create'],
            'openapi_context' => [
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    iri: 'https://schema.org/MediaObject',
    itemOperations: ['get'],
    normalizationContext: ['groups' => ['picture:output']]
)]
class Picture
{
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['picture:output'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['picture:output'])]
    private ?string $path = null;

    #[ApiProperty(iri: 'https://schema.org/contentUrl')]
    #[Groups(['picture:output'])]
    private ?string $contentUrl = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['picture:output'])]
    private ?\DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: Advert::class, inversedBy: 'pictures')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['picture:output'])]
    private $advert = null;

    /**
     * @Vich\UploadableField(mapping="pictures", fileNameProperty="path")
     */
    private ?File $file = null;

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @return string|null
     */
    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    /**
     * @param string|null $contentUrl
     */
    public function setContentUrl(?string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }

    /**
     * @param File|null $file
     */
    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        if($path !== null) {
            $this->path = $path;
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAdvert(): ?Advert
    {
        return $this->advert;
    }

    public function setAdvert(?Advert $advert): self
    {
        $this->advert = $advert;

        return $this;
    }

    public function __toString(): string
    {
        return "$this->path";
    }


}
