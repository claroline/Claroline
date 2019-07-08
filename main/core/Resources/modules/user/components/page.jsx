import React from 'react'
import classes from 'classnames'
import get from 'lodash/get'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {
  ASYNC_BUTTON,
  CALLBACK_BUTTON,
  LINK_BUTTON,
  MODAL_BUTTON,
  URL_BUTTON
} from '#/main/app/buttons'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'
import {PageContent, PageContainer} from '#/main/core/layout/page'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {
  MODAL_USER_PASSWORD,
  MODAL_USER_PUBLIC_URL,
  MODAL_USER_MESSAGE
} from '#/main/core/user/modals'

// TODO : use dynamic actions list

const UserPageHeader = props =>
  <header className={classes('page-header', props.className)}>
    <div className="page-header-picture">
      <UserAvatar
        className="img-thumbnail"
        picture={props.picture}
      />
    </div>

    <div className="page-header-content">
      <h1 className="page-title">
        {props.title}
        &nbsp;
        {props.subtitle && <small>{props.subtitle}</small>}
      </h1>

      {props.children}
    </div>
  </header>

UserPageHeader.propTypes = {
  className: T.string,
  picture: T.shape({
    url: T.string.isRequired
  }),
  title: T.string.isRequired,
  subtitle: T.string,
  children: T.node.isRequired
}

const UserPage = props =>
  <PageContainer
    {...props}
    className="user-page"
  >
    <UserPageHeader
      picture={props.user.picture}
      title={props.user.name}
      subtitle={props.user.username}
    >
      <Toolbar
        id="user-actions"
        className="page-actions"
        tooltip="bottom"
        toolbar="edit | send-message add-contact | more"
        actions={[
          {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: '/edit',
            displayed: hasPermission('edit', props.user),
            primary: true
          }, {
            name: 'send-message',
            type: MODAL_BUTTON,
            label: trans('send_message'),
            icon: 'fa fa-paper-plane-o',
            modal: [MODAL_USER_MESSAGE],
            displayed: hasPermission('contact', props.user)
          }, {
            name: 'add-contact',
            type: CALLBACK_BUTTON,
            label: trans('add_contact'),
            icon: 'fa fa-address-book-o',
            callback: () => true
          }, {
            name: 'change-password',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-lock',
            label: trans('change_password'),
            group: trans('user_management'),
            displayed: hasPermission('administrate', props.user) || props.user.id === get(props.currentUser, 'id'),
            modal: [MODAL_USER_PASSWORD, {
              changePassword: (password) => props.updatePassword(props.user, password)
            }]
          }, {
            name: 'change-url',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-link',
            label: trans('change_profile_public_url'),
            group: trans('user_management'),
            displayed: hasPermission('edit', props.user),
            disabled: props.user.meta.publicUrlTuned,
            modal: [MODAL_USER_PUBLIC_URL, {
              url: props.user.meta.publicUrl,
              changeUrl: (publicUrl) => props.updatePublicUrl(props.user, publicUrl)
            }]
          }, {
            name: 'show-badges',
            type: URL_BUTTON,
            icon: 'fa fa-trophy',
            label: trans('user-badges'),
            group: trans('badges'),
            target: '#/badges/'+props.user.id
          }, {
            name: 'show-tracking',
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-line-chart',
            label: trans('show_tracking'),
            group: trans('user_management'),
            displayed: hasPermission('administrate', props.user),
            target: ['claro_user_tracking', {publicUrl: props.user.meta.publicUrl}]
          }, {
            name: 'delete',
            type: ASYNC_BUTTON,
            icon: 'fa fa-fw fa-trash-o',
            label: trans('delete', {}, 'actions'),
            displayed: hasPermission('delete', props.user),
            request: {
              type: 'delete',
              url: ['apiv2_user_delete_bulk', {ids: [props.user.id]}],
              request: {
                method: 'DELETE'
              }
              //success: () => window.location = url(['claro_desktop_open']) todo redirect
            },
            dangerous: true,
            confirm: {
              title: trans('user_delete_confirm_title'),
              message: trans('user_delete_confirm_message')
            }
          }
        ]}
        scope="object"
      />
    </UserPageHeader>

    <PageContent>
      {props.children}
    </PageContent>
  </PageContainer>

implementPropTypes(UserPage, PageTypes, {
  currentUser: T.object,
  user: T.shape({
    name: T.string.isRequired
  }).isRequired,
  children: T.node.isRequired,
  updatePassword: T.func.isRequired,
  updatePublicUrl: T.func.isRequired,
})

export {
  UserPage
}
