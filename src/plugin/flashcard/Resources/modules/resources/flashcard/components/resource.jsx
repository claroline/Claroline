import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Resource} from '#/main/core/resource'

import {Overview} from '#/plugin/flashcard/resources/flashcard/containers/overview'
import {Player} from '#/plugin/flashcard/resources/flashcard/player/containers/player'
import {PlayerEnd} from '#/plugin/flashcard/resources/flashcard/player/components/end'
import {FlashcardEditor} from '#/plugin/flashcard/resources/flashcard/editor/components/main'

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
      overviewPage={Overview}
      editor={FlashcardEditor}
      pages={[
        {
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
