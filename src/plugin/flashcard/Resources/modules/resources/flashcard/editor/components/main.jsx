import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ResourceEditor} from '#/main/core/resource/editor'

import {selectors} from '#/plugin/flashcard/resources/flashcard/store'
import {FlashcardEditorAppearance} from '#/plugin/flashcard/resources/flashcard/editor/components/appearance'
import {FlashcardEditorCards} from '#/plugin/flashcard/resources/flashcard/editor/components/deck'

const FlashcardEditor = () => {
  const deck = useSelector(selectors.flashcardDeck)

  return (
    <ResourceEditor
      styles={['claroline-distribution-plugin-flashcard-flashcard']}
      additionalData={() => ({
        resource: deck
      })}
      appearancePage={FlashcardEditorAppearance}
      pages={[
        {
          name: 'cards',
          title: trans('cards', {}, 'flashcard'),
          component: FlashcardEditorCards
        }
      ]}
    />
  )
}

export {
  FlashcardEditor
}
