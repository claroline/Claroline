import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {asset} from '#/main/app/config'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, LinkButton, MenuButton, URL_BUTTON} from '#/main/app/buttons'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/community/user/prop-types'
import {constants as userConst} from '#/main/app/user/constants'

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
        <LinkButton className="position-relative" target="/account/profile">
          <UserAvatar picture={props.currentUser.picture} alt={false} />
          <span
            className="app-user-status position-absolute top-100 start-100 translate-middle m-n2 bg-success rounded-circle"
            aria-hidden={true}
          />
        </LinkButton>

        <MenuButton
          id="current-user-menu"
          className="mt-2 text-center"
          menu={{
            className: 'dropdown-menu-full',
            items: [].concat(Object.keys(userConst.USER_STATUSES).map((status) => ({
              name: status,
              type: CALLBACK_BUTTON,
              //active: userConst.USER_STATUS_ONLINE === status,
              //label: userConst.USER_STATUSES[status],
              callback: () => true,
              primary: true,
              label: (
                <div className="d-flex align-items-start">
                  <span className={classes('d-inline-block p-1 my-2 rounded-circle icon-with-text-right', `bg-${userConst.USER_STATUS_COLORS[status]}`)} aria-hidden={true} />

                  <span>
                    {userConst.USER_STATUSES[status]}
                    {userConst.USER_STATUS_OFFLINE === status &&
                      <small className="text-secondary text-wrap d-block">{trans('user_offline_help')}</small>
                    }
                  </span>
                </div>
              )
            })), [
              {
                name: 'profile',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-user',
                label: trans('Mon profile', {}, 'actions'),
                target: '/account/profile'
              }, {
                name: 'parameters',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-sliders',
                label: trans('account', {}, 'context'),
                target: '/account/parameters',
                subscript: {
                  type: 'text',
                  status: 'warning',
                  value: <span className="fa fa-warning" />
                }
              }, {
                name: 'logout',
                type: URL_BUTTON,
                icon: 'fa fa-fw fa-power-off',
                label: trans('logout'),
                target: ['claro_security_logout']
              }
            ])
          }}
        >
          {props.currentUser.name}
          <small className="d-block text-secondary">{userConst.USER_STATUSES[userConst.USER_STATUS_ONLINE]}</small>
        </MenuButton>

        {/*<h1 className="mb-0 mt-2">{props.currentUser.name}</h1>
        <p className="mb-3">En ligne</p>*/}

        {false &&
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
              }, /*{
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
            }, */{
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
                target: '/administration',
                displayed: false
              }
            ]}
          />
        }

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
  registration: T.bool.isRequired,
  closeMenu: T.func
}

export {
  ContextUser
}
