import React from 'react'
import get from 'lodash/get'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceEnd} from '#/main/core/resource/components/end'

import {selectors} from '#/plugin/flashcard/resources/flashcard/store'
import {Timeline} from '#/plugin/flashcard/resources/flashcard/components/timeline'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const PlayerEndComponent = (props) => {
  const attemptData = props.attempt && props.attempt.data
  let session = attemptData ? attemptData.session : 1

  let action = action = {
    type: LINK_BUTTON,
    label: trans('start', {}, 'actions'),
    target: `${props.basePath}`,
    primary: true,
    disabled: props.empty,
    disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')] : []
  }

  if( attemptData.cardsSessionIds.length > 0 ) {
    session = session - 1
  }

  return (
    <ResourceEnd
      contentText={get(props.flashcardDeck, 'end.message')}
      attempt={props.attempt}
      actions={[action]}
      details={[
        [trans('session_indicator', {}, 'flashcard'), session + ' / 7'],
        [trans('session_status', {}, 'flashcard'), trans('session_status_completed', {}, 'flashcard')]
      ]}
    >
      <Timeline
        session={session + 1}
        end={true}
        date={attemptData.date ? attemptData.date.date : null}
      />
    </ResourceEnd>
  )
}

PlayerEndComponent.propTypes = {
  attempt: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  basePath: T.string,
  empty: T.bool,
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired
}

const PlayerEnd = connect(
  (state) => ({
    attempt: selectors.attempt(state),
    flashcardDeck: selectors.flashcardDeck(state)
  })
)(PlayerEndComponent)

export {
  PlayerEnd
}
