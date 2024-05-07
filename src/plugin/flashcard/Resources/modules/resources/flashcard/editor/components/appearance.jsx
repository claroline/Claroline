import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {ResourceEditorAppearance} from '#/main/core/resource/editor'

const FlashcardEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resource.showProgression',
            type: 'boolean',
            label: trans('show_progression', {}, 'flashcard')
          }, {
            name: 'resource.customButtons',
            type: 'boolean',
            label: trans('custom_button_labels', {}, 'flashcard'),
            linked: [
              {
                name: 'resource.rightButtonLabel',
                type: 'string',
                label: trans('right_button_label', {}, 'flashcard'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'customButtons')
              }, {
                name: 'resource.wrongButtonLabel',
                type: 'string',
                label: trans('wrong_button_label', {}, 'flashcard'),
                displayed: (flashcardDeck) => get(flashcardDeck, 'customButtons')
              }
            ]
          }, {
            name: 'resource.showLeitnerRules',
            type: 'boolean',
            label: trans('show_leitner_rules', {}, 'flashcard')
          }
        ]
      }
    ]}
  />

export {
  FlashcardEditorAppearance
}
