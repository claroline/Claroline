import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentSummary} from '#/main/app/content/components/summary'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'

const UserProgressionModal = props => {
  function getStepSummary(step) {
    let resourceEvaluation
    if (!isEmpty(step.primaryResource)) {
      resourceEvaluation = props.resourceEvaluations.find(evaluation => get(evaluation, 'resourceNode.id') === get(step, 'primaryResource.id'))
    }

    return {
      id: step.id,
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-fw fa-circle', props.stepsProgression[step.id]),
      label: (
        <Fragment>
          {step.title}

          {get(props.path, 'display.showScore') && resourceEvaluation && resourceEvaluation.scoreMax &&
            <ScoreBox
              score={get(props.path, 'score.total') ? (resourceEvaluation.score / resourceEvaluation.scoreMax) * get(props.path, 'score.total') : resourceEvaluation.score}
              scoreMax={get(props.path, 'score.total') ? get(props.path, 'score.total') : resourceEvaluation.scoreMax}
              size="sm"
              style={{marginLeft: 'auto'}}
            />
          }
        </Fragment>
      ),
      target: `${props.basePath}/play/${step.slug}`,
      children: step.children ? step.children.map(getStepSummary) : [],
      onClick: () => props.fadeModal()
    }
  }

  return (
    <Modal
      {...omit(props, 'basePath', 'evaluation', 'path', 'stepsProgression', 'fetchUserStepsProgression', 'resetUserStepsProgression')}
      icon="fa fa-fw fa-tasks"
      title={trans('progression')}
      subtitle={props.evaluation.user.name}
      onEntering={() => props.fetchUserStepsProgression(props.path.id, props.evaluation.user.id)}
      onExiting={() => props.resetUserStepsProgression()}
    >
      <div className="modal-body">
        <ContentSummary links={props.path.steps.map(getStepSummary)} />
      </div>
    </Modal>
  )
}

UserProgressionModal.propTypes = {
  basePath: T.string.isRequired,
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ).isRequired,
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  )),
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  stepsProgression: T.object,
  fetchUserStepsProgression: T.func.isRequired,
  resetUserStepsProgression: T.func.isRequired,
  fadeModal: T.func.isRequired
}

UserProgressionModal.defaultProps = {
  resourceEvaluations: []
}

export {
  UserProgressionModal
}
