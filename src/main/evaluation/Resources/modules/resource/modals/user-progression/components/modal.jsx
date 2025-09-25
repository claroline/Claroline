import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {route} from '#/main/core/resource/routing'

import {UserProgressionModal as BaseProgressionModal} from '#/main/evaluation/modals/user-progression/components/modal'
import {getActions} from '#/main/evaluation/resource/utils'
import {ResourceEvaluation} from '#/main/evaluation/resource/prop-types'
import {UserProgressionOverview} from '#/main/evaluation/resource/modals/user-progression/components/overview'
import {UserProgressionArchives} from '#/main/evaluation/resource/modals/user-progression/components/archives'

const STORE_NAME = 'userResourceEvaluation'

const UserProgressionModal = props => {
  const currentUser = useSelector(securitySelectors.currentUser)

  return (
    <BaseProgressionModal
      {...omit(props, 'evaluation')}
      evaluation={props.evaluation}
      name={STORE_NAME}
      title={trans('resource_name', {
        type: trans(get(props.evaluation, 'resourceNode.meta.type'), {}, 'resource'),
        name: get(props.evaluation, 'resourceNode.name')
      }, 'resource')}
      url={['apiv2_resource_evaluation_get', {evaluationId: get(props.evaluation, 'id')}]}
      actions={getActions([props.evaluation], {}, route(get(props.evaluation, 'resourceNode')), currentUser)}
      additional={[
        {
          icon: 'fa fa-rotate-right',
          label: trans('attempts', {}, 'evaluation'),
          value: get(props.evaluation, 'nbAttempts', 0)
        }
      ]}
      overview={UserProgressionOverview}
      archives={UserProgressionArchives}
    />
  )
}

UserProgressionModal.propTypes = {
  evaluation: T.shape(
    ResourceEvaluation.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  UserProgressionModal
}
