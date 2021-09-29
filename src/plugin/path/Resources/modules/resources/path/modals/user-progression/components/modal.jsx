import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ContentSummary} from '#/main/app/content/components/summary'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'

import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'

const UserProgressionModal = props => {
  function getStepSummary(step) {
    return {
      id: step.id,
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-fw fa-circle', props.stepsProgression[step.id]),
      label: step.title,
      target: `${props.basePath}/play/${step.slug}`,
      children: step.children ? step.children.map(getStepSummary) : [],
      onClick: () => props.fadeModal()
    }
  }

  return (
    <Modal
      {...omit(props, 'evaluation', 'path', 'stepsProgression', 'fetchUserStepsProgression', 'resetUserStepsProgression')}
      icon="fa fa-fw fa-tasks"
      title={trans('progression')}
      subtitle={props.evaluation.user.name}
      onEntering={() => {
        props.fetchUserStepsProgression(props.path.id, props.evaluation.user.id)
      }}
      onExiting={() => {
        props.resetUserStepsProgression()
      }}
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
    UserEvaluationTypes.propTypes
  ).isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  stepsProgression: T.object,
  fetchUserStepsProgression: T.func.isRequired,
  resetUserStepsProgression: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  UserProgressionModal
}
