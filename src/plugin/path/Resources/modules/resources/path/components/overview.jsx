import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'
import {PathSummary} from '#/plugin/path/resources/path/components/summary'

const PathOverview = props => {
  return (
    <ResourceOverview
      contentText={get(props.path, 'overview.message')}
      evaluation={props.evaluation}
      resourceNode={props.resourceNode}
      display={{
        score: get(props.path, 'display.showScore'),
        scoreMax: get(props.path, 'score.total'),
        successScore: get(props.path, 'score.success'),
        feedback: !!get(props.path, 'evaluation.successMessage') || !!get(props.path, 'evaluation.failureMessage')
      }}
      feedbacks={{
        success: get(props.path, 'evaluation.successMessage'),
        failure: get(props.path, 'evaluation.failureMessage')
      }}
      actions={[
        {
          type: LINK_BUTTON,
          label: trans('start_path', {}, 'path'),
          target: `${props.basePath}/play`,
          primary: true,
          disabled: props.empty,
          disabledMessages: props.empty ? [trans('start_disabled_empty', {}, 'path')]:[]
        }
      ]}
    >
      <section className="resource-parameters">
        {!isEmpty(get(props.path, 'overview.resource')) &&
          <ResourceEmbedded
            className="step-primary-resource"
            resourceNode={get(props.path, 'overview.resource')}
            showHeader={false}
          />
        }

        <h3 className="h2">{trans('summary')}</h3>

        {!isEmpty(props.path.steps) &&
          <PathSummary
            className="component-container"
            basePath={props.basePath}
            path={props.path}
            stepsProgression={props.stepsProgression}
            resourceEvaluations={props.resourceEvaluations}
            onNavigate={() => {
              scrollTo(`#resource-${props.resourceId} > .page-content`)
            }}
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
    ResourceEvaluationTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  )),
  stepsProgression: T.object,
  resourceNode: T.object
}

PathOverview.defaultProps = {
  empty: true,
  evaluation: {},
  resourceEvaluations: []
}

export {
  PathOverview
}
