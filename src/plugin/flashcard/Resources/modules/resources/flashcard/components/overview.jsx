import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {FlashcardDeck as FlashcardDeckTypes} from '#/plugin/flashcard/resources/flashcard/prop-types'
import {FlashcardInfo} from '#/plugin/flashcard/resources/flashcard/components/info'

const Overview = (props) =>
  <ResourceOverview
    contentText={get(props.flashcardDeck, 'overview.message')}
    evaluation={props.evaluation}
    resourceNode={props.resourceNode}
    actions={[{
      type: LINK_BUTTON,
      label: trans('start', {}, 'actions'),
      target: `${props.basePath}/play`,
      primary: true,
      disabled: props.empty,
      disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'flashcard')] : []
    }]}
  >
    <section
      className="resource-parameters mb-3">
      <div
        className="resource-column col-md-8">
        <FlashcardInfo
          flashcard={props.flashcardDeck}
        />
      </div>
    </section>
  </ResourceOverview>

Overview.propTypes = {
  basePath: T.string.isRequired,
  flashcardDeck: T.shape(
    FlashcardDeckTypes.propTypes
  ).isRequired,
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  empty: T.bool.isRequired,
  resourceNode: T.object
}

Overview.defaultProps = {
  empty: true
}

export {
  Overview
}
