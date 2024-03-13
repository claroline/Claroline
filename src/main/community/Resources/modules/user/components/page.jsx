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
import {User as UserTypes} from '#/main/community/user/prop-types'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {ContentLoader} from '#/main/app/content/components/loader'

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
      }
    ].concat(props.user ? props.breadcrumb : [])}
    icon={
      <UserAvatar user={!isEmpty(props.user) ? props.user : undefined} size="xl" />
    }
    title={get(props.user, 'name', trans('loading'))}
    poster={get(props.user, 'poster')}
    toolbar="edit | send-message add-contact | fullscreen more"
    actions={!isEmpty(props.user) ? getActions([props.user], {
      add: () => props.reload(props.user.id),
      update: () => props.reload(props.user.id),
      delete: () => props.reload(props.user.id)
    }, props.path, props.currentUser) : []}
  >
    {isEmpty(props.user) &&
      <ContentLoader
        size="lg"
        description={trans('user_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.user) && props.children}
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
