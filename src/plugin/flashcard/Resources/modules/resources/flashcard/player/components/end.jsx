import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {ResourceEnd} from '#/main/core/resource/components/end'

import {selectors as baseSelectors} from '#/plugin/flashcard/resources/flashcard/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {FlashcardInfo} from '#/plugin/flashcard/resources/flashcard/components/info'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

class PlayerEndComponent extends Component {
  render() {
    return (
      <ResourceEnd
        contentText={get(this.props.flashcardDeck, 'end.message')}
        feedbacks={{}}
        attempt={this.props.evaluation}
      >
        <section className="resource-parameters mb-3">
          <FlashcardInfo
            flashcard={this.props.flashcard}
          />
        </section>
      </ResourceEnd>
    )
  }
}

PlayerEndComponent.propTypes = {
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  flashcard: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  )
}

const PlayerEnd = connect(
  (state) => ({
    flashcardDeck: baseSelectors.flashcardDeck(state),
    evaluation: resourceSelectors.resourceEvaluation(state)
  })
)(PlayerEndComponent)

export {
  PlayerEnd
}
