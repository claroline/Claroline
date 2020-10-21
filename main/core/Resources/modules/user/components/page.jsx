import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {PageFull} from '#/main/app/page/components/full'

import {User as UserTypes} from '#/main/core/user/prop-types'
import {getActions} from '#/main/core/user/utils'
import {route} from '#/main/core/user/routing'
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
    subtitle={props.user.username}
    icon={
      <UserAvatar className="user-avatar-lg img-thumbnail" picture={props.user.picture} />
    }
    header={{
      title: `${trans('user_profile')} - ${props.user.name}`,
      description: get(props.user, 'meta.description')
    }}
    toolbar="edit | send-message add-contact | more"
    actions={
      getActions([props.user], {
        add: () => false,
        update: (users) => props.history.push(route(users[0])),
        delete: () => false
      }, props.path, props.currentUser)
        .then(actions => [
          {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: props.path + '/edit',
            displayed: hasPermission('edit', props.user),
            primary: true
          }
        ].concat(actions))
    }
  >
    {props.children}
  </PageFull>

UserPage.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  currentUser: T.object,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  children: T.node.isRequired,
  path: T.string.isRequired,
  showBreadcrumb: T.bool.isRequired,
  breadcrumb: T.arrayOf(T.shape({
    type: T.string,
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  }))
}

UserPage.defaultProps = {
  breadcrumb: []
}

export {
  UserPage
}
