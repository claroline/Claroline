import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Overview} from '#/plugin/flashcard/resources/flashcard/containers/overview'
import {Editor} from '#/plugin/flashcard/resources/flashcard/editor/components/editor'
import {Player} from '#/plugin/flashcard/resources/flashcard/player/containers/player'
import {PlayerEnd} from '#/plugin/flashcard/resources/flashcard/player/components/end'

const FlashcardResource = props =>
  <ResourcePage
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: props.path,
        displayed: props.overview,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play',
        label: trans('start', {}, 'actions'),
        target: `${props.path}/play`
      }
    ]}
    routes={[
      {
        path: '/edit',
        component: Editor,
        disabled: !props.editable
      }, {
        path: '/play/end',
        exact: true,
        component: PlayerEnd
      }, {
        path: '/play',
        exact: true,
        component: Player,
        onEnter: () => {
          props.startAttempt(props.flashcardDeck.id)
        }
      }, {
        path: '/',
        component: Overview,
        disabled: !props.overview
      }
    ]}
    redirect={[
      {
        from: '/',
        exact: true,
        to: '/play',
        disabled: props.overview
      }
    ]}
  />

FlashcardResource.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
  overview: T.bool.isRequired,
  startAttempt: T.func,
  flashcardDeck: T.shape({
    id: T.string
  })
}

export {
  FlashcardResource
}
