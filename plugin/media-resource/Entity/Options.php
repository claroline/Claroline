<?php

namespace Innova\MediaResourceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Options for a Media Resource.
 *
 * @ORM\Table(name="media_resource_options")
 * @ORM\Entity
 */
class Options implements \JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * View mode for the ressource.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    protected $mode;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default":false})
     */
    protected $showTextTranscription;

    /**
     * The language to use for tts in the form language-region ([ISO 639-1 alpha-2][-][ISO 3166-1 alpha-2]).
     * Examples: 'en', 'en-US', 'en-GB', 'zh-CN'.
     *
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    protected $ttsLanguage;

    // play mode possible values
    const FREE = 'free';
    const CONTINUOUS_PAUSE = 'pause';
    const CONTINUOUS_LIVE = 'live';
    const CONTINUOUS_ACTIVE = 'active';
    const SCRIPTED_ACTIVE = 'scripted_active';

    // TTS lang possible values
    const EN_US = 'en-US';
    const EN_GB = 'en-GB';
    const DE_DE = 'de-DE';
    const ES_ES = 'es-ES';
    const FR_FR = 'fr-FR';
    const IT_IT = 'it-IT';

    public function __construct()
    {
        $this->setMode(self::FREE);
        $this->setShowTextTranscription(false);
        $this->setTtsLanguage(self::EN_US);
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setShowTextTranscription($showTextTranscription)
    {
        $this->showTextTranscription = $showTextTranscription;

        return $this;
    }

    public function getShowTextTranscription()
    {
        return $this->showTextTranscription;
    }

    public function setTtsLanguage($ttsLanguage)
    {
        $this->ttsLanguage = $ttsLanguage;

        return $this;
    }

    public function getTtsLanguage()
    {
        return $this->ttsLanguage;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'lang' => $this->ttsLanguage,
            'showTextTranscription' => $this->showTextTranscription,
            'mode' => $this->mode,
        ];
    }
}
