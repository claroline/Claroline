import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ResourceEnd} from '#/main/core/resource/components/end'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors} from '#/plugin/flashcard/resources/flashcard/store'
import {
  FlashcardDeck as FlashcardDeckTypes,
  FlashcardDeckProgression as FlashcardDeckProgressionTypes
} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {FlashcardInfo} from '#/plugin/flashcard/resources/flashcard/components/info'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

const PlayerEndComponent = (props) =>
  <ResourceEnd
    contentText={get(props.flashcardDeck, 'end.message')}
    feedbacks={{}}
    attempt={props.evaluation}
  >
    <FlashcardInfo
      flashcardDeck={props.flashcardDeck}
      flashcardDeckProgression={props.flashcardDeckProgression}
    />
  </ResourceEnd>

PlayerEndComponent.propTypes = {
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  flashcardDeckProgression: T.arrayOf(
    T.shape(
      FlashcardDeckProgressionTypes.propTypes
    )
  )
}

const PlayerEnd = connect(
  (state) => ({
    flashcardDeck: selectors.flashcardDeck(state),
    evaluation: resourceSelectors.resourceEvaluation(state),
    flashcardDeckProgression: selectors.flashcardDeckProgression(state)
  })
)(PlayerEndComponent)

export {
  PlayerEnd
}
