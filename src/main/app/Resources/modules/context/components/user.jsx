import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {url} from '#/main/app/api'
import {asset} from '#/main/app/config'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, LinkButton, MenuButton, URL_BUTTON} from '#/main/app/buttons'

import {UserAvatar} from '#/main/app/user/components/avatar'
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

    <div className="btn-toolbar gap-1 mt-3 justify-content-end" role="presentation">
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

  const poster = props.poster || get(props.currentUser, 'poster')

  return (
    <>
      <div
        className={classes('app-menu-cover', !isEmpty(poster) && 'app-menu-poster')}
        style={!isEmpty(poster) ? {
          backgroundImage: `url(${asset(poster)})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        } : undefined}
        role="presentation"
      />

      <article className="app-menu-current-user">
        <UserAvatar user={props.currentUser} noStatusTooltip={true} size="lg" />

        <MenuButton
          id="current-user-menu"
          className="app-menu-user mt-2 text-center"
          menu={{
            //className: 'dropdown-menu-full',
            items: [].concat(Object.keys(userConst.USER_STATUSES).map((status) => ({
              name: status,
              type: CALLBACK_BUTTON,
              callback: () => props.changeStatus(props.currentUser, status),
              primary: true,
              label: (
                <div className="d-flex align-items-start" role="presentation">
                  <span className={classes('d-inline-block p-1 my-2 rounded-circle icon-with-text-right', `bg-${userConst.USER_STATUS_COLORS[status]}`)} aria-hidden={true} />

                  <span role="presentation">
                    {userConst.USER_STATUSES[status]}
                    {userConst.USER_STATUS_OFFLINE === status &&
                      <small className="text-body-secondary text-wrap d-block">{trans('user_offline_help')}</small>
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
                target: props.path+'/profile'
              }, {
                name: 'parameters',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-sliders',
                label: trans('account', {}, 'context'),
                target: '/account'/*,
                subscript: {
                  type: 'text',
                  status: 'warning',
                  value: <span className="fa fa-warning" />
                }*/
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
          <small className="d-block">{userConst.USER_STATUSES[props.currentUser.status]}</small>
        </MenuButton>
      </article>
    </>
  )
}

ContextUser.propTypes = {
  path: T.string.isRequired,
  authenticated: T.bool.isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  impersonated: T.bool.isRequired,
  registration: T.bool.isRequired,
  changeStatus: T.func.isRequired,
  closeMenu: T.func
}

export {
  ContextUser
}
