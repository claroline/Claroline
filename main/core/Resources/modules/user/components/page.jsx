import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'

import {getActions} from '#/main/core/user/utils'
import {route} from '#/main/core/user/routing'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'
import {PageSimple} from '#/main/app/page/components/simple'
import {PageContent} from '#/main/core/layout/page'
import {UserAvatar} from '#/main/core/user/components/avatar'

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
  <PageSimple
    className="user-page"
    showBreadcrumb={props.showBreadcrumb}
    path={props.breadcrumb.concat([{
      label: props.user.name,
      target: ''
    }])}
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
              }/*, {
                name: 'add-contact',
                type: CALLBACK_BUTTON,
                label: trans('add_contact'),
                icon: 'fa fa-address-book-o',
                callback: () => true,
                displayed: false  // TODO : restore
              }, {
                name: 'show-badges',
                type: URL_BUTTON,
                icon: 'fa fa-trophy',
                label: trans('user-badges'),
                group: trans('badges'),
                target: '#/badges/'+props.user.id,
                displayed: false // TODO : restore
              }*/
            ].concat(actions.map((action, index) => {
              return {
                name: `action-${index}`,
                type: action.type,
                icon: action.icon,
                label: action.label,
                displayed: action.displayed,
                disabled: action.disabled,
                dangerous: action.dangerous,
                primary: action.primary,
                target: action.target,
                modal: action.modal,
                callback: action.callback,
                request: action.request,
                confirm: action.confirm
              }
            })))
        }
        scope="object"
      />
    </UserPageHeader>

    <PageContent>
      {props.children}
    </PageContent>
  </PageSimple>

implementPropTypes(UserPage, PageTypes, {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  currentUser: T.object,
  user: T.shape({
    name: T.string.isRequired
  }).isRequired,
  children: T.node.isRequired,
  path: T.string.isRequired,
  showBreadcrumb: T.bool.isRequired,
  breadcrumb: T.array // TODO : correct prop type
}, {
  breadcrumb: []
})

export {
  UserPage
}
