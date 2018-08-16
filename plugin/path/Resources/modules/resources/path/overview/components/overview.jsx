import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

import {Summary} from '#/plugin/path/resources/path/overview/components/summary'
import {selectors} from '#/plugin/path/resources/path/store'
import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'

const OverviewComponent = props =>
  <ResourceOverview
    contentText={props.path.display.description}
    progression={{
      status: props.evaluation ? props.evaluation.status : undefined,
      score: props.evaluation ? {
        displayed: true,
        current: props.evaluation.score,
        total: props.evaluation.scoreMax
      } : undefined
    }}
    actions={[
      { // TODO : implement continue and restart
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-play icon-with-text-right',
        label: trans('start_path', {}, 'path'),
        target: '/play',
        primary: true,
        disabled: props.empty,
        disabledMessages: props.empty ? [trans('start_disabled_empty')]:[]
      }
    ]}
  >
    <section className="resource-parameters">
      <h3 className="h2">{trans('summary')}</h3>

      <Summary
        steps={props.path.steps}
      />
    </section>
  </ResourceOverview>

OverviewComponent.propTypes = {
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  empty: T.bool.isRequired,
  evaluation: T.shape(
    UserEvaluationTypes.propTypes
  )
}

OverviewComponent.defaultProps = {
  empty: true,
  evaluation: {}
}

const Overview = connect(
  (state) => ({
    path: selectors.path(state),
    empty: selectors.empty(state),
    evaluation: resourceSelectors.resourceEvaluation(state)
  })
)(OverviewComponent)

export {
  Overview
}
