import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {route} from '#/main/core/resource/routing'

import {UserProgressionModal as BaseProgressionModal} from '#/main/evaluation/modals/user-progression/components/modal'
import {getAttemptActions} from '#/main/evaluation/resource/utils'
import {ResourceAttempt} from '#/main/evaluation/resource/prop-types'
import {UserAttemptOverview} from '#/main/evaluation/resource/modals/user-attempt/components/overview'

const STORE_NAME = 'userResourceAttempt'

const UserAttemptModal = props => {
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
      url={['apiv2_resource_attempt_get', {attemptId: get(props.evaluation, 'id')}]}
      actions={getAttemptActions([props.evaluation], {}, route(get(props.evaluation, 'resourceNode')), currentUser)}
      overview={UserAttemptOverview}
    />
  )
}

UserAttemptModal.propTypes = {
  evaluation: T.shape(
    ResourceAttempt.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired
}

export {
  UserAttemptModal
}
