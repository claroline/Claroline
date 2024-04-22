import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource, ResourcePage} from '#/main/core/resource'

import {Overview} from '#/plugin/flashcard/resources/flashcard/containers/overview'
import {Editor} from '#/plugin/flashcard/resources/flashcard/editor/components/editor'
import {Player} from '#/plugin/flashcard/resources/flashcard/player/containers/player'
import {PlayerEnd} from '#/plugin/flashcard/resources/flashcard/player/components/end'


const FlashcardResource = props => {
  useEffect(() => {
    if (!props.empty && props.flashcardDeck) {
      props.getAttempt(props.flashcardDeck.id)
    }
  }, [props.empty, props.flashcardDeck.id])

  return (
    <Resource
      {...omit(props, 'editable', 'empty', 'overview', 'getAttempt', 'flashcardDeck')}
      styles={['claroline-distribution-plugin-flashcard-flashcard']}
      overview={Overview}
      /*actions={[
        {
          name: 'play',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-play',
          label: trans('start', {}, 'actions'),
          target: `${props.path}/play`,
          displayed: !props.empty
        }
      ]}*/
      pages={[
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
          disabled: props.empty
        }
      ]}
      redirect={[
        { from: '/', exact: true, to: '/play', disabled: props.overview || props.empty }
      ]}
    />
  )
}
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
