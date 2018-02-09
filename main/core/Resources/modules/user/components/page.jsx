import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {Page as PageTypes} from '#/main/core/layout/page/prop-types'
import {PageContent} from '#/main/core/layout/page'
import {RoutedPage} from '#/main/core/layout/router'

import {UserPageActions} from '#/main/core/user/components/page-actions.jsx'
import {UserAvatar} from '#/main/core/user/components/avatar.jsx'

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
  <RoutedPage
    {...props}
    className="user-page"
  >
    <UserPageHeader
      picture={props.user.picture}
      title={props.user.name}
      subtitle={props.user.username}
    >
      <UserPageActions
        user={props.user}
        showModal={props.showModal}
        customActions={props.customActions}
        updatePassword={props.updatePassword}
      />
    </UserPageHeader>

    <PageContent>
      {props.children}
    </PageContent>
  </RoutedPage>

implementPropTypes(UserPage, PageTypes, {
  user: T.shape({
    name: T.string.isRequired
  }).isRequired,
  /**
   * Custom actions for the user added by the UI.
   */
  customActions: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    label: T.string.isRequired,
    disabled: T.bool,
    displayed: T.bool,
    action: T.oneOfType([T.string, T.func]).isRequired,
    dangerous: T.bool,
    group: T.string
  })),
  children: T.node.isRequired,
  updatePassword: T.func.isRequired
}, {
  customActions: []
})

export {
  UserPage
}
