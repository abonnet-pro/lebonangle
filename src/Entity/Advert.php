<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\AdvertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertRepository::class)]
#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get'],
    denormalizationContext: ['groups' => ['advert:input']],
    normalizationContext: ['groups' => ['advert:output']],
    order: ['createdAt' => 'asc']
)]
#[ApiFilter(OrderFilter::class, properties: ['publishedAt', 'price'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(SearchFilter::class, properties: ['category.name' => 'exact'])]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
class Advert
{
    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->pictures = new ArrayCollection();
    }

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue]
    #[Groups(['advert:output'])]
    private ?int $id = null;

    #[Assert\Length(min: 3, max: 100,)]
    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Groups(['advert:input', 'advert:output'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text', length: 1200, nullable: false)]
    #[Groups(['advert:input', 'advert:output'])]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['advert:input', 'advert:output'])]
    private ?string $author = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Groups(['advert:input', 'advert:output'])]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'adverts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['advert:input', 'advert:output'])]
    private ?Category $category = null;

    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} euros and {{ max }} euros to enter',
        min: 1.00,
        max: 1000000.00,
    )]
    #[ORM\Column(type: 'float', nullable: false)]
    #[Groups(['advert:input', 'advert:output'])]
    private ?float $price = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => 'draft'])]
    private ?string $state = 'draft';

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['advert:output'])]
    private ?\DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['advert:output'])]
    private ?\DateTime $publishedAt = null;

    #[ORM\OneToMany(mappedBy: 'advert', targetEntity: Picture::class, cascade: ['all'])]
    #[Groups(['advert:input', 'advert:output'])]
    private $pictures;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function __toString(): string
    {
        return "$this->title";
    }

    /**
     * @return Collection|Picture[]
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setAdvert($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getAdvert() === $this) {
                $picture->setAdvert(null);
            }
        }

        return $this;
    }
}
