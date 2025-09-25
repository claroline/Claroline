import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {UserProgressionModal as BaseProgressionModal} from '#/main/evaluation/modals/user-progression/components/modal'

import {route} from '#/main/evaluation/sequence'
import {getEvaluationActions} from '#/main/evaluation/sequence/utils'
import {SequenceEvaluation as SequenceEvaluationTypes} from '#/main/evaluation/sequence/prop-types'
import {UserProgressionOverview} from '#/main/evaluation/sequence/modals/user-progression/components/overview'
import {UserProgressionCertificates} from '#/main/evaluation/sequence/modals/user-progression/components/certificates'
import {UserProgressionArchives} from '#/main/evaluation/sequence/modals/user-progression/components/archives'

const STORE_NAME = 'userSequenceEvaluation'

const UserProgressionModal = props => {
  const currentUser = useSelector(securitySelectors.currentUser)

  return (
    <BaseProgressionModal
      {...omit(props, 'evaluation', 'path', 'fetchUserStepsProgression', 'resetUserStepsProgression')}
      evaluation={props.evaluation}
      name={STORE_NAME}
      title={trans('sequence_name', {name: get(props.evaluation, 'sequence.name')}, 'evaluation')}
      url={['apiv2_sequence_evaluation_get', {evaluationId: get(props.evaluation, 'id')}]}
      actions={getEvaluationActions([props.evaluation], {}, route(get(props.evaluation, 'sequence')), currentUser)}
      overview={UserProgressionOverview}
      archives={UserProgressionArchives}
      tabs={[
        {
          name: 'certificates',
          title: trans('certificates', {}, 'evaluation'),
          displayed: !!props.evaluation.certified,
          component: UserProgressionCertificates
        }
      ]}
    />
  )
}

UserProgressionModal.propTypes = {
  evaluation: T.shape(
    SequenceEvaluationTypes.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  UserProgressionModal
}
