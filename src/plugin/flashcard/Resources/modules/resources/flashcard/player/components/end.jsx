import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ResourceEnd} from '#/main/core/resource/components/end'

import {selectors} from '#/plugin/flashcard/resources/flashcard/store'
import {
  FlashcardDeck as FlashcardDeckTypes,
  FlashcardProgression as FlashcardProgressionTypes
} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {FlashcardInfo} from '#/plugin/flashcard/resources/flashcard/components/info'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

const PlayerEndComponent = (props) =>
  <ResourceEnd
    contentText={get(props.flashcardDeck, 'end.message')}
    feedbacks={{}}
    attempt={props.attempt}
  >
    <FlashcardInfo
      flashcardDeck={props.flashcardDeck}
      flashcardProgression={props.flashcardProgression}
    />
  </ResourceEnd>

PlayerEndComponent.propTypes = {
  attempt: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  flashcardProgression: T.arrayOf(
    T.shape(
      FlashcardProgressionTypes.propTypes
    )
  )
}

const PlayerEnd = connect(
  (state) => ({
    attempt: selectors.attempt(state),
    flashcardDeck: selectors.flashcardDeck(state),
    flashcardProgression: selectors.flashcardProgression(state)
  })
)(PlayerEndComponent)

export {
  PlayerEnd
}
