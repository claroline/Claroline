import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

import {Timeline} from '#/plugin/flashcard/resources/flashcard/components/timeline'
import {LeitnerRules} from '#/plugin/flashcard/resources/flashcard/components/rules'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {PageSection} from '#/main/app/page/components/section'

const Overview = (props) => {
  const attemptData = props.attempt && props.attempt.data
  const sessionStarted = attemptData ? attemptData.cardsAnsweredIds.length > 0 : false
  const sessionCompleted = attemptData ? attemptData.cardsSessionIds.length === 0 : false
  const session = attemptData ? attemptData.session : 1

  let statusText = trans('session_status_next', {}, 'flashcard')
  let action = action = {
    name: 'start',
    type: LINK_BUTTON,
    label: trans('start', {}, 'actions'),
    target: `${props.basePath}/play`,
    primary: true,
    disabled: props.empty,
    disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')] : []
  }

  if (sessionCompleted) {
    statusText = trans('session_status_completed', {}, 'flashcard')
    action = {
      name: 'start',
      type: LINK_BUTTON,
      label: trans('restart', {}, 'actions'),
      target: `${props.basePath}/play`,
      primary: true,
      disabled: props.empty,
      disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')] : []
    }
  } else if (sessionStarted) {
    statusText = trans('session_status_current', {}, 'flashcard')
    action = {
      name: 'start',
      type: LINK_BUTTON,
      label: trans('continue', {}, 'actions'),
      target: `${props.basePath}/play`,
      primary: true
    }
  }

  return (
    <ResourceOverview
      primaryAction="start"
      evaluation={props.evaluation}
      attempt={props.attempt}
      actions={[action]}
      details={[
        [trans('session_indicator', {}, 'flashcard'), session + ' / 7'],
        [trans('session_status', {}, 'flashcard'), statusText]
      ]}
    >

      <PageSection size="md" className="py-3">
        {isEmpty(props.cards) &&
          <ContentPlaceholder
            size="lg"
            title={trans('no_cards', {}, 'flashcard')}
          />
        }

        {!isEmpty(props.cards) &&
          <Timeline
            session={session}
            started={sessionStarted}
            completed={sessionCompleted}
          />
        }

        {props.flashcardDeck.showLeitnerRules && !isEmpty(props.cards) &&
          <LeitnerRules
            session={session}
            completed={sessionCompleted}
          />
        }
      </PageSection>
    </ResourceOverview>
  )
}

Overview.propTypes = {
  basePath: T.string.isRequired,
  attempt: T.shape({
    status: T.string,
    data: T.shape({
      nextCardIndex: T.number,
      session: T.number,
      cards: T.array
    })
  }),
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  empty: T.bool.isRequired,
  resourceNode: T.object,
  cards: T.array
}

Overview.defaultProps = {
  empty: true
}

export {
  Overview
}
