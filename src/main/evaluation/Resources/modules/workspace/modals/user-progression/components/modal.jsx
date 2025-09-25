import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {UserProgressionModal as BaseProgressionModal} from '#/main/evaluation/modals/user-progression/components/modal'

import {route} from '#/main/evaluation/workspace/routing'
import {getEvaluationActions} from '#/main/evaluation/workspace/utils'
import {WorkspaceEvaluation} from '#/main/evaluation/workspace/prop-types'
import {UserProgressionOverview} from '#/main/evaluation/workspace/modals/user-progression/components/overview'
import {UserProgressionCertificates} from '#/main/evaluation/workspace/modals/user-progression/components/certificates'
import {UserProgressionArchives} from '#/main/evaluation/workspace/modals/user-progression/components/archives'

const STORE_NAME = 'userWorkspaceEvaluation'

const UserProgressionModal = props => {
  const currentUser = useSelector(securitySelectors.currentUser)

  return (
    <BaseProgressionModal
      {...omit(props)}
      evaluation={props.evaluation}
      name={STORE_NAME}
      title={trans('workspace_name', {name: get(props.evaluation, 'workspace.name')}, 'workspace')}
      url={['apiv2_workspace_evaluation_get', {evaluationId: get(props.evaluation, 'id')}]}
      actions={getEvaluationActions([props.evaluation], {}, route(props.evaluation), currentUser)}
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
    WorkspaceEvaluation.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  UserProgressionModal
}
