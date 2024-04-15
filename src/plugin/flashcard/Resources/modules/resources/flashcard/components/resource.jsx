import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource, ResourcePage} from '#/main/core/resource'

import {Overview} from '#/plugin/flashcard/resources/flashcard/containers/overview'
import {Editor} from '#/plugin/flashcard/resources/flashcard/editor/components/editor'
import {Player} from '#/plugin/flashcard/resources/flashcard/player/containers/player'
import {PlayerEnd} from '#/plugin/flashcard/resources/flashcard/player/components/end'


const FlashcardResource = props =>
  <Resource
    {...omit(props, 'editable', 'empty', 'overview', 'getAttempt', 'flashcardDeck')}
    styles={['claroline-distribution-plugin-flashcard-flashcard']}
    overview={Overview}
  >
    <ResourcePage
      primaryAction="play"
      actions={[
        {
          name: 'play',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-play',
          label: trans('start', {}, 'actions'),
          target: `${props.path}/play`,
          displayed: !props.empty
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
          disabled: props.empty,
          component: PlayerEnd
        }, {
          path: '/play',
          exact: true,
          component: Player,
          disabled: props.empty,
          onEnter: () => {
            props.getAttempt(props.flashcardDeck.id)
          }
        }, {
          path: '/',
          component: Overview,
          disabled: !props.overview,
          onEnter: () => {
            props.getAttempt(props.flashcardDeck.id)
          }
        }
      ]}
      redirect={[
        { from: '/', exact: true, to: '/play', disabled: props.overview || props.empty }
      ]}
    />
  </Resource>

FlashcardResource.propTypes = {
  path: T.string.isRequired,
  editable: T.bool.isRequired,
  empty: T.bool.isRequired,
  overview: T.bool.isRequired,
  getAttempt: T.func,
  flashcardDeck: T.shape({
    id: T.string,
    cards: T.array
  })
}

export {
  FlashcardResource
}
