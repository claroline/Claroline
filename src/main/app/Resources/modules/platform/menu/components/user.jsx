import React, {useCallback, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {Menu} from '#/main/app/overlays/menu'

import {UserAvatar} from '#/main/app/user/components/avatar'
import {constants as userConst} from '#/main/app/user/constants'
import {displayUsername} from '#/main/community/utils'
import {route, selectors as contextSelectors} from '#/main/app/context'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions, selectors} from '#/main/app/platform/store'
import {CloseButton} from 'react-bootstrap'

const UserMenu = (props) => {
  const dispatch = useDispatch()
  const availableContexts = useSelector(selectors.availableContexts)

  const accountLinks = [
    {
      name: 'profile',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-user',
      label: trans('my_profile'),
      target: props.path+'/profile'
    }, {
      name: 'parameters',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-sliders',
      label: trans('parameters'),
      target: '/account'
    }
  ]

  const changeStatus = useCallback((status) => {
    dispatch(actions.changeStatus(props.user, status))
  }, [props.user.id])

  return (
    <Menu className="app-user-menu flyout-menu p-0 position-fixed">
      <div className="flyout-menu-content d-flex flex-column flex-fill" role="presentation">
        <div className="flyout-menu-close fs-sm rounded-pill bg-body position-absolute end-0 top-0 z-1" role="presentation">
          <CloseButton onClick={props.closeMenu} className="rounded-circle" aria-label={trans('close', {}, 'actions')} />
        </div>

        {get(props.user, 'poster') &&
          <Thumbnail thumbnail={get(props.user, 'poster')} />
        }

        <div
          className={classes('d-flex flex-column px-3 align-items-start', {
            'pt-3': !get(props.user, 'poster')
          })}
          role="presentation"
          style={get(props.user, 'poster') ? {marginTop: '-3.5rem'} : undefined}
        >
          <UserAvatar
            user={props.user}
            size="md"
            border={true}
          />

          <h2 className="h5 mb-0 mt-3">{displayUsername(props.user)}</h2>
          <p className="text-body-secondary ">{props.user.username}</p>
        </div>

        <div className="list-group mx-3 mb-3">
          <Button
            className="list-group-item list-group-item-action focus-ring d-flex align-items-center"
            type={MENU_BUTTON}
            icon={classes(`fa fa-fw fa-${userConst.USER_STATUS_ICONS[get(props.user, 'status')]}`, `text-${userConst.USER_STATUS_COLORS[get(props.user, 'status')]}`)}
            label={userConst.USER_STATUSES[get(props.user, 'status')]}
            containerClassName="w-100"
            menu={{
              items: Object.keys(userConst.USER_STATUSES).map(status => ({
                type: CALLBACK_BUTTON,
                icon: classes(`fa fa-fw fa-${userConst.USER_STATUS_ICONS[status]}`, `text-${userConst.USER_STATUS_COLORS[status]}`),
                label: userConst.USER_STATUSES[status],
                callback: () => changeStatus(status),
                children: userConst.USER_STATUS_OFFLINE === status &&
                  <small className="ms-3 ps-3 text-body-secondary text-wrap d-block">{trans('user_offline_help')}</small>
              }))
            }}
          >
            <span className="fa fa-chevron-right ms-auto text-body-tertiary" aria-hidden={true} />
          </Button>
        </div>

        <div className="list-group mb-3 mx-3">
          {availableContexts
            .filter(appContext => 'workspace' !== appContext.name)
            .map(appContext =>
              <Button
                key={appContext.name}
                className="list-group-item list-group-item-action"
                type={LINK_BUTTON}
                icon={`fa fa-fw fa-${appContext.icon}`}
                label={trans(appContext.name, {}, 'context')}
                exact={true}
                target={route(appContext.name)}
                onClick={props.closeMenu}
              />
            )
          }
        </div>

        <div className="list-group mx-3 mb-3">
          {accountLinks.map((accountLink) => (
            <Button
              key={accountLink.name}
              {...accountLink}
              className="list-group-item list-group-item-action focus-ring"
              onClick={props.closeMenu}
            />
          ))}
        </div>

        <div className="list-group mb-3 mt-auto mx-3">
          <Button
            className="list-group-item list-group-item-action focus-ring"
            onClick={props.closeMenu}
            {...{
              name: 'logout',
              type: URL_BUTTON,
              icon: 'fa fa-fw fa-power-off',
              label: trans('logout'),
              target: ['claro_security_logout']
            }}
          />

          {props.impersonated &&
            <Button
              className="list-group-item list-group-item-action focus-ring"
              onClick={props.closeMenu}
              {...{
                type: URL_BUTTON,
                icon: 'fa fa-fw fa-times',
                label: trans('exit_impersonation', {}, 'actions'),
                target: url(['claro_index', {_switch: '_exit'}])+'#'+props.path
              }}
            />
          }
        </div>
      </div>
    </Menu>
  )
}

UserMenu.propTypes = {
  user: T.object.isRequired,
  impersonated: T.bool,
  path: T.string,
  closeMenu: T.func.isRequired
}

const PlatformMenuUser = (props) => {
  const currentUser = useSelector(securitySelectors.currentUser)
  const impersonated = useSelector(securitySelectors.isImpersonated)
  const path = useSelector(contextSelectors.path)

  const [menuOpened, setMenuOpened] = useState(false)

  return (
    <Button
      type={MENU_BUTTON}
      label={trans(impersonated ? 'impersonated_account' : 'my_account', {name: displayUsername(currentUser)})}
      tooltip={props.vertical ? 'right' : 'top'}
      className="app-context-btn focus-ring rounded-circle"
      opened={menuOpened}
      onToggle={setMenuOpened}
      menu={{
        drop: 'end',
        render: () => (
          <UserMenu
            impersonated={impersonated}
            user={currentUser}
            path={path}
            closeMenu={() => setMenuOpened(false)}
          />
        )
      }}
    >
      <UserAvatar user={currentUser} size="sm" />
    </Button>
  )
}

export {
  PlatformMenuUser
}
