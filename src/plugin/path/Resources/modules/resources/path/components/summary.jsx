import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {ContentSummary} from '#/main/app/content/components/summary'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EvaluationScore} from '#/main/evaluation/components/score'

import {constants} from '#/main/evaluation/constants'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'
import {number, trans} from '#/main/app/intl'
import {constants as PATH_NUMBERINGS} from '#/plugin/path/resources/path/constants'
import {getNumbering} from '#/plugin/path/resources/path/utils'

const PathSummary = (props) => {
  function getStepSummary(step) {
    let resourceEvaluation
    if (!isEmpty(step.primaryResource) && !isEmpty(props.resourceEvaluations)) {
      resourceEvaluation = props.resourceEvaluations.find(evaluation => get(evaluation, 'resourceNode.id') === get(step, 'primaryResource.id'))
    }

    return {
      id: step.id,
      type: LINK_BUTTON,
      label: (
        <Fragment>
          {(props.path.display.numbering && props.path.display.numbering !== PATH_NUMBERINGS.NUMBERING_NONE && getNumbering(props.path.display.numbering, props.path.steps, step) ?
            <span className="h-numbering">{getNumbering(props.path.display.numbering, props.path.steps, step)}</span>
            : ''
          )}
          {step.title}

          <span className="step-status">
            {!resourceEvaluation &&
              <span className={classes('fa fa-fw', {
                // status for steps without required resource
                'fa-circle not_started': !props.stepsProgression[step.id] || ['unseen', 'to_do'].includes(props.stepsProgression[step.id]),
                'fa-circle-check': ['seen', 'done'].includes(props.stepsProgression[step.id]),
                'fa-circle-xmark': ['to_review'].includes(props.stepsProgression[step.id])
              })} />
            }

            {resourceEvaluation &&
              <Fragment>
                {get(props.path, 'display.showScore') && resourceEvaluation.scoreMax &&
                  <EvaluationScore
                    score={get(props.path, 'score.total') ? (resourceEvaluation.score / resourceEvaluation.scoreMax) * get(props.path, 'score.total') : resourceEvaluation.score}
                    scoreMax={get(props.path, 'score.total') ? get(props.path, 'score.total') : resourceEvaluation.scoreMax}
                    size="sm"
                  />
                }

                {!resourceEvaluation.scoreMax && [constants.EVALUATION_STATUS_INCOMPLETE].includes(resourceEvaluation.status) &&
                  <span className="step-progression">{number(resourceEvaluation.progression) || '0'} %</span>
                }

                <span className={classes('fa fa-fw icon-with-text-left', {
                  // status for steps with a required resource
                  'fa-circle not_started': [constants.EVALUATION_STATUS_NOT_ATTEMPTED, constants.EVALUATION_STATUS_TODO, constants.EVALUATION_STATUS_OPENED].includes(resourceEvaluation.status),
                  'fa-circle-notch fa-spin': [constants.EVALUATION_STATUS_INCOMPLETE].includes(resourceEvaluation.status),
                  'fa-circle-check': [constants.EVALUATION_STATUS_COMPLETED, constants.EVALUATION_STATUS_PARTICIPATED, constants.EVALUATION_STATUS_PASSED].includes(resourceEvaluation.status),
                  'fa-circle-xmark': constants.EVALUATION_STATUS_FAILED === resourceEvaluation.status
                })} />
              </Fragment>
            }
          </span>
        </Fragment>
      ),
      target: `${props.basePath}/play/${step.slug}`,
      children: step.children ? step.children.map(getStepSummary) : [],
      onClick: props.onNavigate
    }
  }

  let baseLinks = []
  if (props.overview) {
    baseLinks = [{
      id: 'home',
      type: LINK_BUTTON,
      label: (
        <Fragment>
          {trans('home')}

          <span className="step-status">
            <span className={classes('fa fa-fw fa-home')} />
          </span>
        </Fragment>
      ),
      target: props.basePath,
      exact: true,
      onClick: props.onNavigate
    }]
  }

  let endLink = []
  if (props.showEndPage) {
    endLink = [{
      id: 'end',
      type: LINK_BUTTON,
      label:(
        <Fragment>
          {trans('end')}
          <span className="step-status">
            <span className={classes('fa fa-fw fa-flag-checkered', {
              'not_started': Object.values(props.stepsProgression).length <= 0 || Object.values(props.stepsProgression).map( (step) => ['seen'].includes(step) ).length !== props.path.steps.length
            } )} />
          </span>
        </Fragment>
      ),
      target: props.basePath + '/play/end',
      exact: true,
      onClick: props.onNavigate
    }]
  }

  return (
    <ContentSummary
      className={props.className}
      links={baseLinks.concat(
        props.path.steps.map(getStepSummary)
      ).concat(endLink)}
      noCollapse={true}
    />
  )
}

PathSummary.propTypes = {
  className: T.string,
  basePath: T.string.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  stepsProgression: T.object,
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  )),
  onNavigate: T.func,
  overview: T.bool,
  showEndPage: T.bool
}

export {
  PathSummary
}
