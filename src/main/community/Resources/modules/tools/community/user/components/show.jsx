import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ToolPage} from '#/main/core/tool'

import {User as UserTypes} from '#/main/community/user/prop-types'
import {getActions} from '#/main/community/user/utils'
import {UserProfile} from '#/main/community/user/components/profile'
import {pickAction} from '#/main/app/action'

const UserShow = (props) => {
  const userActions = getActions([props.user], {
    add: () => props.reload(props.user.id),
    update: () => props.reload(props.user.id),
    delete: () => props.reload(props.user.id)
  }, props.path, props.currentUser)

  let banner
  if (get(props.user, 'restrictions.disabled', false)) {
    banner = {
      type: 'danger',
      content: trans('user_disabled_info', {}, 'community'),
      actions: Promise.all([
        pickAction('enable', userActions),
        pickAction('delete', userActions)
      ])
    }
  }

  return (
    <ToolPage
      title={trans('user_name', {name: get(props.user, 'name', trans('loading'))}, 'community')}
      description={get(props.user, 'meta.description')}
      banner={banner}
    >
      <UserProfile
        path={props.path}
        user={props.user}
        addGroups={props.addGroups}
        primaryAction="send-message"
        actions={userActions}
      />
    </ToolPage>
  )
}

UserShow.propTypes = {
  path: T.string.isRequired,
  user: T.shape(
    UserTypes.propTypes
  ),
  currentUser: T.object,
  reload: T.func.isRequired,
  addGroups: T.func.isRequired
}

export {
  UserShow
}
