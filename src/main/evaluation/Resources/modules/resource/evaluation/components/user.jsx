import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {ResourcePage} from '#/main/core/resource'
import {UserAvatar} from '#/main/app/user/components/avatar'


const ResourceEvaluationsUser = (props) =>
  <ResourcePage
    icon={
      <UserAvatar user={!isEmpty(props.user) ? props.user : undefined} size="xl" />
    }
  >

  </ResourcePage>

export {
  ResourceEvaluationsUser
}
