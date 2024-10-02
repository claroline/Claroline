import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {getActions} from '#/main/community/user/utils'
import {User as UserTypes} from '#/main/community/user/prop-types'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageHeading} from '#/main/app/page/components/heading'

const User = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('users', {}, 'community'),
        target: `${props.path}/users`
      }
    ].concat(props.user ? props.breadcrumb : [])}
    poster={get(props.user, 'poster')}
    title={get(props.user, 'name', trans('loading'))}
    description={get(props.group, 'meta.description')}
  >
    {isEmpty(props.user) &&
      <ContentLoader
        size="lg"
        description={trans('user_loading', {}, 'community')}
      />
    }

    {!isEmpty(props.user) &&
      <PageHeading
        size="md"
        icon={
          <UserAvatar user={!isEmpty(props.user) ? props.user : undefined} size="xl" />
        }
        title={get(props.user, 'name', trans('loading'))}
        primaryAction="send-message"
        actions={!isEmpty(props.user) ? getActions([props.user], {
          add: () => props.reload(props.user.id),
          update: () => props.reload(props.user.id),
          delete: () => props.reload(props.user.id)
        }, props.path, props.currentUser) : []}
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
