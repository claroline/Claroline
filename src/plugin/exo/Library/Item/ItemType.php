<?php

namespace UJM\ExoBundle\Library\Item;

/**
 * References the types of Item managed by the bundle.
 */
final class ItemType
{
    /**
     * The user has to choose one (or many) proposition(s) in a set.
     */
    public const CHOICE = 'application/x.choice+json';

    /**
     * The user has to fill hole(s) in a text.
     */
    public const CLOZE = 'application/x.cloze+json';

    /**
     * The user has to find element(s) on an image.
     */
    public const GRAPHIC = 'application/x.graphic+json';

    /**
     * The user has to associate elements together.
     */
    public const MATCH = 'application/x.match+json';

    /**
     * The user has to associate one element to another.
     */
    public const PAIR = 'application/x.pair+json';

    /**
     * The user has to classify elements into categories.
     */
    public const SET = 'application/x.set+json';

    /**
     * The user has to write his answer using predefined keywords.
     */
    public const WORDS = 'application/x.words+json';

    /**
     * The user has to write his answer.
     */
    public const OPEN = 'application/x.open+json';

    public const CONTENT = 'content';

    /**
     * The user has to write his answer using predefined keywords in a grid.
     */
    public const GRID = 'application/x.grid+json';

    /**
     * The user has to sort items.
     */
    public const ORDERING = 'application/x.ordering+json';

    /**
     * The user has to write his answer.
     */
    public const WAVEFORM = 'application/x.waveform+json';

    /**
     * Get the list of managed item types.
     */
    public static function getList(): array
    {
        return [
            ItemType::CHOICE,
            ItemType::CLOZE,
            ItemType::GRAPHIC,
            ItemType::MATCH,
            ItemType::PAIR,
            ItemType::SET,
            ItemType::WORDS,
            ItemType::OPEN,
            ItemType::GRID,
            ItemType::CONTENT,
            ItemType::ORDERING,
            ItemType::WAVEFORM,
        ];
    }

    public static function isSupported(string $type): bool
    {
        return in_array($type, ItemType::getList());
    }
}
