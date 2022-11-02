import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PageFull} from '#/main/app/page/components/full'

import {User as UserTypes} from '#/main/community/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'

const UserPage = props =>
  <PageFull
    className="user-page"
    showBreadcrumb={props.showBreadcrumb}
    path={props.breadcrumb.concat([{
      label: props.user.name,
      target: ''
    }])}
    title={props.user.name}
    subtitle={props.title || props.user.username}
    poster={props.user.poster}
    icon={
      <UserAvatar className="user-avatar-lg img-thumbnail" picture={props.user.picture} />
    }
    meta={{
      title: `${trans('user_profile')} - ${props.user.name}`,
      description: get(props.user, 'meta.description')
    }}
    toolbar={props.toolbar}
    actions={props.actions}
  >
    {props.children}
  </PageFull>

UserPage.propTypes = {
  toolbar: T.string,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  title: T.string,
  showBreadcrumb: T.bool.isRequired,
  actions: T.any,
  breadcrumb: T.arrayOf(T.shape({
    type: T.string,
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),
  children: T.node.isRequired
}

UserPage.defaultProps = {
  breadcrumb: []
}

export {
  UserPage
}
