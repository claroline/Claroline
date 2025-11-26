<?php

namespace Claroline\AuthenticationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Disabled;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'claro_authentication_oauth')]
class OAuthClient
{
    use Id;
    use Uuid;
    use Name;
    use Disabled;

    #[ORM\Column(type: Types::STRING)]
    private ?string $serviceProvider = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $clientId = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $clientSecret = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $urlAuthorize = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $urlAccessToken = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $urlResourceOwnerDetails = null;

    /**
     * Specific parameters for the service provider (e.g., tenant for Azure).
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $additionalParameters = [];

    /**
     * A mapping between the resource owner info provided by the OAuth client and the claroline User.
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $fieldsMapping = [];

    /**
     * Create new users when they log with the provider.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $createOnLogin = false;

    /**
     * Enable disabled users when they log with the provider.
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $reactivateOnLogin = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $buttonDisplayed = true;

    #[ORM\Column(nullable: true)]
    private ?string $buttonIcon = null;

    #[ORM\Column(nullable: true)]
    private ?string $buttonLabel = null;

    #[ORM\Column(nullable: true)]
    private ?string $buttonConfirm = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Organization $organization = null;

    public function getServiceProvider(): string
    {
        return $this->serviceProvider;
    }

    public function setServiceProvider(string $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    public function getUrlAuthorize(): ?string
    {
        return $this->urlAuthorize;
    }

    public function setUrlAuthorize(?string $urlAuthorize): void
    {
        $this->urlAuthorize = $urlAuthorize;
    }

    public function getUrlAccessToken(): ?string
    {
        return $this->urlAccessToken;
    }

    public function setUrlAccessToken(?string $urlAccessToken): void
    {
        $this->urlAccessToken = $urlAccessToken;
    }

    public function getUrlResourceOwnerDetails(): ?string
    {
        return $this->urlResourceOwnerDetails;
    }

    public function setUrlResourceOwnerDetails(?string $urlResourceOwnerDetails): void
    {
        $this->urlResourceOwnerDetails = $urlResourceOwnerDetails;
    }

    public function getAdditionalParameters(): ?array
    {
        return $this->additionalParameters;
    }

    public function getAdditionalParameter(string $name): mixed
    {
        if ($this->additionalParameters && array_key_exists($name, $this->additionalParameters)) {
            return $this->additionalParameters[$name];
        }

        return null;
    }

    public function setAdditionalParameters(?array $additionalParameters): void
    {
        $this->additionalParameters = $additionalParameters;
    }

    public function getFieldsMapping(): ?array
    {
        return $this->fieldsMapping;
    }

    public function setFieldsMapping(?array $fieldsMapping): void
    {
        $this->fieldsMapping = $fieldsMapping;
    }

    public function getCreateOnLogin(): bool
    {
        return $this->createOnLogin;
    }

    public function setCreateOnLogin(bool $createOnLogin): void
    {
        $this->createOnLogin = $createOnLogin;
    }

    public function getReactivateOnLogin(): bool
    {
        return $this->reactivateOnLogin;
    }

    public function setReactivateOnLogin(bool $reactivateOnLogin): void
    {
        $this->reactivateOnLogin = $reactivateOnLogin;
    }

    public function isButtonDisplayed(): bool
    {
        return $this->buttonDisplayed;
    }

    public function setButtonDisplayed(bool $buttonDisplayed): void
    {
        $this->buttonDisplayed = $buttonDisplayed;
    }

    public function getButtonIcon(): ?string
    {
        return $this->buttonIcon;
    }

    public function setButtonIcon(?string $buttonIcon): void
    {
        $this->buttonIcon = $buttonIcon;
    }

    public function getButtonLabel(): ?string
    {
        return $this->buttonLabel;
    }

    public function setButtonLabel(?string $buttonLabel): void
    {
        $this->buttonLabel = $buttonLabel;
    }

    public function getButtonConfirm(): ?string
    {
        return $this->buttonConfirm;
    }

    public function setButtonConfirm(?string $buttonConfirm): void
    {
        $this->buttonConfirm = $buttonConfirm;
    }

    public function getOrganizations(): array
    {
        return [$this->organization];
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }
}
