import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/user/utils'
import {route} from '#/main/community/user/routing'
import {User as UserTypes} from '#/main/community/user/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'

const User = (props) =>
  <ToolPage
    className="user-page"
    meta={{
      title: get(props.user, 'name'),
      description: get(props.user, 'meta.description')
    }}
    path={[
      {
        type: LINK_BUTTON,
        label: trans('users', {}, 'community'),
        target: `${props.path}/users`
      }, {
        type: LINK_BUTTON,
        label: get(props.user, 'name', trans('loading')),
        target: !isEmpty(props.user) ? route(props.user, props.path) : ''
      }
    ].concat(props.user ? props.breadcrumb : [])}
    icon={
      <UserAvatar className="user-avatar-lg img-thumbnail" picture={get(props.user, 'picture')} />
    }
    title={get(props.user, 'name', trans('loading'))}
    subtitle={get(props.user, 'username')}
    poster={get(props.user, 'poster')}
    toolbar="edit | send-message add-contact | fullscreen more"
    actions={!isEmpty(props.user) ? getActions([props.user], {
      add: props.reload,
      update: props.reload,
      delete: props.reload
    }, props.path, props.currentUser) : []}
  >
    {props.children}
  </ToolPage>

User.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  user: T.shape(
    UserTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any,
  reload: T.func
}

User.defaultProps = {
  breadcrumb: []
}

const UserPage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(User)

export {
  UserPage
}
