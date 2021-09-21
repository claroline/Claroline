import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentSummary} from '#/main/app/content/components/summary'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'

const PathOverview = props => {
  function getStepSummary(step) {
    return {
      id: step.id,
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-fw fa-circle', get(step, 'userProgression.status')),
      label: (
        <Fragment>
          {step.title}
          {step.evaluated && get(props.path, 'display.showScore') && get(props.attempt, `data.resources[${step.id}].max`, null) &&
            <ScoreBox
              score={get(props.attempt, `data.resources[${step.id}].score`, null)}
              scoreMax={get(props.attempt, `data.resources[${step.id}].max`)}
              size="sm"
              style={{marginLeft: 'auto'}}
            />
          }
        </Fragment>
      ),
      target: `${props.basePath}/play/${step.slug}`,
      children: step.children ? step.children.map(getStepSummary) : [],
      onClick: () => {
        scrollTo(`#resource-${props.resourceId} > .page-content`)
      }
    }
  }

  return (
    <ResourceOverview
      contentText={get(props.path, 'meta.description')}
      evaluation={props.evaluation}
      display={{
        score: get(props.path, 'display.showScore'),
        scoreMax: get(props.path, 'score.total')
      }}

      actions={[
        { // TODO : implement continue and restart
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-play icon-with-text-right',
          label: trans('start_path', {}, 'path'),
          target: `${props.basePath}/play`,
          primary: true,
          disabled: props.empty,
          disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'path')]:[]
        }
      ]}
    >
      <section className="resource-parameters">
        <h3 className="h2">{trans('summary')}</h3>

        {!isEmpty(props.path.steps) &&
          <ContentSummary
            className="component-container"
            links={props.path.steps.map(getStepSummary)}
          />
        }

        {isEmpty(props.path.steps) &&
          <ContentPlaceholder
            size="lg"
            title={trans('no_step', {}, 'path')}
          />
        }
      </section>
    </ResourceOverview>
  )
}

PathOverview.propTypes = {
  basePath: T.string.isRequired,
  resourceId: T.string.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  empty: T.bool.isRequired,
  evaluation: T.shape(
    UserEvaluationTypes.propTypes
  ),
  attempt: T.object
}

PathOverview.defaultProps = {
  empty: true,
  evaluation: {}
}

export {
  PathOverview
}
