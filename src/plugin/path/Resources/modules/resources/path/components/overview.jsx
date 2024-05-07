import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEmbedded} from '#/main/core/resource/containers/embedded'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'
import {PathSummary} from '#/plugin/path/resources/path/containers/summary'
import {PageSection} from '#/main/app/page/components/section'

const PathOverview = (props) =>
  <ResourceOverview
    evaluation={props.evaluation}
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
        name: 'start',
        type: LINK_BUTTON,
        label: trans('start', {}, 'actions'),
        target: `${props.basePath}/play`,
        primary: true,
        disabled: props.empty
      }
    ]}
  >
    {!isEmpty(get(props.path, 'overview.resource')) &&
      <PageSection size="md">
        <ResourceEmbedded
          className="step-primary-resource"
          resourceNode={get(props.path, 'overview.resource')}
          showHeader={false}
        />
      </PageSection>
    }

    <PageSection
      size="md"
      className="py-3"
      title={trans('summary')}
    >
      {!isEmpty(props.path.steps) ?
        <PathSummary /> :
        <ContentPlaceholder
          size="lg"
          title={trans('no_step', {}, 'path')}
        />
      }
    </PageSection>
  </ResourceOverview>

PathOverview.propTypes = {
  basePath: T.string.isRequired,
  resourceId: T.string.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  overview: T.bool.isRequired,
  showEndPage: T.bool.isRequired,
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
