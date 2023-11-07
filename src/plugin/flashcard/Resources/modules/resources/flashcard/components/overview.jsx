import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {FlashcardInfo} from '#/plugin/flashcard/resources/flashcard/components/info'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'

const Overview = (props) => {

  let action = null

  if (props.attempt?.data?.nextCardIndex > 0) {
    action = {
      type: LINK_BUTTON,
      label: trans('continue', {}, 'actions'),
      target: `${props.basePath}/play`,
      primary: true
    }
  } else if (props.attempt?.data?.session === 7 && props.attempt?.status === 'completed') {
    action = {
      type: LINK_BUTTON,
      label: trans('restart', {}, 'actions'),
      target: `${props.basePath}/play`,
      primary: true,
      disabled: props.empty,
      disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')] : []
    }
  } else {
    action = {
      type: LINK_BUTTON,
      label: trans('start', {}, 'actions'),
      target: `${props.basePath}/play`,
      primary: true,
      disabled: props.empty,
      disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')] : []
    }
  }

  return (
    <ResourceOverview
      contentText={get(props.flashcardDeck, 'overview.message')}
      evaluation={props.evaluation}
      attempt={props.attempt}
      resourceNode={props.resourceNode}
      actions={[action]}
    >
      <h5>
        Session {props.attempt?.data?.nextCardIndex === 0 ? (props.attempt?.data?.session + ((props.attempt?.status === 'completed') ? ' terminée' : ' à venir')) : props.attempt?.data?.session + ' en cours'}
      </h5>

      <FlashcardInfo
        flashcardProgression={props.flashcardProgression}
      />
    </ResourceOverview>
  )
}

Overview.propTypes = {
  basePath: T.string.isRequired,
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  empty: T.bool.isRequired,
  resourceNode: T.object
}

Overview.defaultProps = {
  empty: true
}

export {
  Overview
}
