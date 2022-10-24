<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(null, message: "Vous devez indiquer votre email")]
    #[Assert\Length(min: 5, max: 250, minMessage: 'Votre email doit contenir au moins 5 caractères', maxMessage: 'Votre email ne peut pas dépasser 250 caractère')]
    #[Assert\Email(message: "Votre email n'est pas valide")]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank(null, message: "Vous devez indiquer votre mot de passe")]
    #[Assert\Regex(pattern: "#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W)#", message: "Votre mot de passe doit contenir au moins une minuscule, une majuscule, un chiffre et un caractère spéciale.")]
    #[Assert\Length(min: 8, minMessage: "Votre mot de passe doit contenir au moins 8 caractères.")]
    private string $password;

    private string $oldPassword;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $num_Internal_user;


    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $gender;

    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\NotBlank(null, message: "Vous devez indiquer votre prénom")]
    private ?string $firstname;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private ?string $lastname;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email2;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $birthday_at;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $avatar;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'created_by')]
    private ?User $users_create;

    #[ORM\OneToMany(mappedBy: 'users_create', targetEntity: self::class)]
    private Collection $created_by;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'updated_by')]
    private $users_update;

    #[ORM\OneToMany(mappedBy: 'users_update', targetEntity: self::class)]
    private Collection $updated_by;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updated_at;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $token;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Token::class, orphanRemoval: true)]
    private Collection $tokens;

    #[ORM\Column(type: 'boolean')]
    private ?bool $actived;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserSession::class, orphanRemoval: true)]
    private Collection $userSessions;

    #[ORM\Column(type: 'boolean')]
    private ?bool $cgu;

    #[ORM\Column(type: 'boolean')]
    private ?bool $share_data;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isGoogle;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isFacebook;

    public function __construct()
    {
        $this->created_by = new ArrayCollection();
        $this->updated_by = new ArrayCollection();
        $this->tokens = new ArrayCollection();
        $this->userSessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNumInternalUser(): ?string
    {
        return $this->num_Internal_user;
    }

    public function setNumInternalUser(?string $num_Internal_user): self
    {
        $this->num_Internal_user = $num_Internal_user;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(?string $email2): self
    {
        $this->email2 = $email2;

        return $this;
    }

    public function getBirthdayAt(): ?\DateTimeInterface
    {
        return $this->birthday_at;
    }

    public function setBirthdayAt(?\DateTimeInterface $birthday_at): self
    {
        $this->birthday_at = $birthday_at;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getUsersCreate(): ?self
    {
        return $this->users_create;
    }

    public function setUsersCreate(?self $users_create): self
    {
        $this->users_create = $users_create;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getCreatedBy(): Collection
    {
        return $this->created_by;
    }

    public function addCreatedBy(self $createdBy): self
    {
        if (!$this->created_by->contains($createdBy)) {
            $this->created_by[] = $createdBy;
            $createdBy->setUsersCreate($this);
        }

        return $this;
    }

    public function removeCreatedBy(self $createdBy): self
    {
        if ($this->created_by->removeElement($createdBy)) {
            // set the owning side to null (unless already changed)
            if ($createdBy->getUsersCreate() === $this) {
                $createdBy->setUsersCreate(null);
            }
        }

        return $this;
    }

    public function getUsersUpdate(): ?self
    {
        return $this->users_update;
    }

    public function setUsersUpdate(?self $users_update): self
    {
        $this->users_update = $users_update;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUpdatedBy(): Collection
    {
        return $this->updated_by;
    }

    public function addUpdatedBy(self $updatedBy): self
    {
        if (!$this->updated_by->contains($updatedBy)) {
            $this->updated_by[] = $updatedBy;
            $updatedBy->setUsersUpdate($this);
        }

        return $this;
    }

    public function removeUpdatedBy(self $updatedBy): self
    {
        if ($this->updated_by->removeElement($updatedBy)) {
            // set the owning side to null (unless already changed)
            if ($updatedBy->getUsersUpdate() === $this) {
                $updatedBy->setUsersUpdate(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection<int, Token>
     */
    public function getTokens(): Collection
    {
        return $this->tokens;
    }

    public function addToken(Token $token): self
    {
        if (!$this->tokens->contains($token)) {
            $this->tokens[] = $token;
            $token->setUser($this);
        }

        return $this;
    }

    public function removeToken(Token $token): self
    {
        if ($this->tokens->removeElement($token)) {
            // set the owning side to null (unless already changed)
            if ($token->getUser() === $this) {
                $token->setUser(null);
            }
        }

        return $this;
    }

    public function getActived(): ?bool
    {
        return $this->actived;
    }

    public function setActived(bool $actived): self
    {
        $this->actived = $actived;

        return $this;
    }

    /**
     * Get the value of oldPassword
     *
     * @return  string
     */
    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    /**
     * Set the value of oldPassword
     *
     * @param  string  $oldPassword
     *
     * @return  self
     */
    public function setOldPassword(string $oldPassword)
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }
  
    /**
     * @return Collection<int, UserSession>
     */
    public function getUserSessions(): Collection
    {
        return $this->userSessions;
    }

    public function addUserSession(UserSession $userSession): self
    {
        if (!$this->userSessions->contains($userSession)) {
            $this->userSessions[] = $userSession;
            $userSession->setUser($this);
        }

        return $this;
    }

    public function removeUserSession(UserSession $userSession): self
    {
        if ($this->userSessions->removeElement($userSession)) {
            // set the owning side to null (unless already changed)
            if ($userSession->getUser() === $this) {
                $userSession->setUser(null);
            }
        }

        return $this;
    }

    public function getCgu(): ?bool
    {
        return $this->cgu;
    }

    public function setCgu(bool $cgu): self
    {
        $this->cgu = $cgu;

        return $this;
    }

    public function getShareData(): ?bool
    {
        return $this->share_data;
    }

    public function setShareData(bool $share_data): self
    {
        $this->share_data = $share_data;

        return $this;
    }

    public function getIsGoogle(): ?bool
    {
        return $this->isGoogle;
    }

    public function setIsGoogle(?bool $isGoogle): self
    {
        $this->isGoogle = $isGoogle;

        return $this;
    }

    public function getIsFacebook(): ?bool
    {
        return $this->isFacebook;
    }

    public function setIsFacebook(?bool $isFacebook): self
    {
        $this->isFacebook = $isFacebook;

        return $this;
    }
}
