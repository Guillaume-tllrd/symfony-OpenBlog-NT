<?php
// cette page avec le repesitory a été créé à partir de la commande dans le terminal "symfony console make:user" Users pour le nom de la table et nickname pour l'authentification qui est l'identifiant unique
namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['nickname'], message: 'Il y a déjà un compte avec ce pseudo')] // créé avec symfony control make: registration-form
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue] //autoincrémentation
    #[ORM\Column] //création de la colone dans la bdd
    private ?int $id = null;

    // pour la validation du nombre de caractère on utilise #[Assert\Length], On sait que c'est Limit car c'est écrit dans la doc
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(
        min: 3,
        max: 25,
        minMessage: 'Minimum {{ limit }} caractères',
        maxMessage: 'Maximum {{ limit }} caractères'
    )]
    private ?string $nickname = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    //pour rajouter un message au moment de la validation de l'email on utilise Symfony\Component\Validator\Constraints as Assert; que l'on est allé chercher sur la doc. Ca permet de mettre un type Email directement dans l'entité
    #[Assert\Email(
        message: 'L\'adresse e-mail {{ value }} est incorrecte'
    )]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isVerified = false; //isverifed est faux tant que la personne n'a pas vérifié son compte car il ne peut pas être null

    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Posts::class, orphanRemoval: true)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Comments::class, orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->nickname;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER, n'importe quel user à le role user
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Posts>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Posts $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUsers($this);
        }

        return $this;
    }

    public function removePost(Posts $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUsers() === $this) {
                $post->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comments>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comments $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUsers($this);
        }

        return $this;
    }

    public function removeComment(Comments $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUsers() === $this) {
                $comment->setUsers(null);
            }
        }

        return $this;
    }
}
//  ensuite pour faire la migration il faut se mettre sur le container php : docker-compose exec php /bin /bash et faire la commande : symfony console make:migration puis symfony console doctrine:migrations:migrate