import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {asset} from '#/main/app/config'
import {Button, Toolbar} from '#/main/app/action'
import {LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/community/user/prop-types'

import {MODAL_HISTORY} from '#/plugin/history/modals/history'
import {MODAL_FAVOURITES} from '#/plugin/favourite/modals/favourites'

const ContextAuthentication = (props) =>
  <Toolbar
    className="d-grid gap-1 m-3"
    variant="btn"
    onClick={props.closeMenu}
    actions={[
      {
        name: 'login',
        type: LINK_BUTTON,
        label: trans('login', {}, 'actions'),
        target: '/login',
        size: 'lg',
        primary: true
      }, {
        name: 'create-account',
        type: LINK_BUTTON,
        label: trans('create-account', {}, 'actions'),
        target: '/registration',
        displayed: props.registration
      }
    ]}
  />

ContextAuthentication.propTypes = {
  registration: T.bool.isRequired,
  closeMenu: T.func
}

const ContextImpersonation = (props) =>
  <div>
    {trans('impersonation_mode_alert')}

    <div className="btn-toolbar gap-1 mt-3 justify-content-end">
      <Button
        className="btn btn-warning"
        type={URL_BUTTON}
        label={trans('exit', {}, 'actions')}
        target={url(['claro_index', {_switch: '_exit'}])+'#'+location.pathname}
        onClick={props.closeMenu}
      />
    </div>
  </div>

ContextImpersonation.propTypes = {

}

const ContextUser = (props) => {
  if (!props.authenticated) {
    return (
      <ContextAuthentication
        registration={props.registration}
      />
    )
  }

  return (
    <>
      <div
        className={classes('app-menu-cover', !isEmpty(props.currentUser.thumbnail) && 'app-menu-poster')}
        style={!isEmpty(props.currentUser.thumbnail) && {
          backgroundImage: `url(${asset(props.currentUser.thumbnail)})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        }}
      >
        {props.children}
      </div>

      <article className="app-menu-current-user">
        <UserAvatar picture={props.currentUser.picture} alt={false} />

        <h1 className="mb-0 mt-1">{props.currentUser.name}</h1>
        <p className="mb-3">{props.title}</p>

        <Toolbar
          className="d-flex flex-direction-row justify-content-between"
          buttonName="btn btn-menu"
          onClick={props.closeMenu}
          tooltip="bottom"
          actions={[
            {
              name: 'profile',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-user',
              label: trans('Mon profile', {}, 'actions'),
              target: '/account/profile'
            }, {
              name: 'notifications',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-bell',
              label: trans('notifications', {}, 'notification'),
              target: '/registration',
              subscript: {
                type: 'label',
                status: 'primary',
                value: '99+'
              }
            }, {
              name: 'history',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-history',
              label: trans('history', {}, 'history'),
              modal: [MODAL_HISTORY]
            }, {
              name: 'favorites',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-star',
              label: trans('favourites', {}, 'favourite'),
              modal: [MODAL_FAVOURITES]
            }, {
              name: 'parameters',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-sliders',
              label: trans('Mon compte', {}, 'actions'),
              target: '/account',
              subscript: {
                type: 'text',
                status: 'warning',
                value: <span className="fa fa-warning" />
              }
            }, {
              name: 'administration',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-wrench',
              label: trans('Administration', {}, 'actions'),
              target: '/administration'
            }
          ]}
        />
      </article>
    </>
  )
}

ContextUser.propTypes = {
  authenticated: T.bool.isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  impersonated: T.bool.isRequired,
  unavailable: T.bool.isRequired,
  registration: T.bool.isRequired,
  closeMenu: T.func
}

export {
  ContextUser
}
