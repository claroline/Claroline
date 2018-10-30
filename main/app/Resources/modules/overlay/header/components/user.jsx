import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {constants as roleConstants} from '#/main/core/user/role/constants'

// TODO : add user poster when available

const UserMenu = props =>
  <div className="app-current-user dropdown-menu dropdown-menu-right">
    <div className="app-current-user-header">
      <div className="app-current-user-icon">
        <UserAvatar picture={props.currentUser.picture} alt={true} />
      </div>

      <h2 className="h4">
        {props.currentUser.name}
      </h2>

      <em>
        {props.currentUser.roles
          .filter(role => -1 !== [roleConstants.ROLE_PLATFORM, roleConstants.ROLE_CUSTOM].indexOf(role.type))
          .map(role => trans(role.translationKey)).join(', ')
        }
      </em>
    </div>

    {!props.authenticated &&
      <div className="app-current-user-body">
        <Button
          type={URL_BUTTON}
          className="btn btn-block btn-emphasis"
          label={trans('login', {}, 'actions')}
          primary={true}
          target={props.login}
        />

        {props.registration &&
          <Button
            type={URL_BUTTON}
            className="btn btn-block"
            label={trans('self-register', {}, 'actions')}
            target={props.registration}
          />
        }
      </div>
    }

    <div className="app-current-user-tools list-group">
      {props.authenticated &&
        <Button
          type={URL_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-user"
          label={trans('user_profile')}
          target={['claro_user_profile', {user: props.currentUser.publicUrl}]}
        />
      }

      {props.authenticated &&
        <Button
          type={URL_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-cog"
          label={trans('parameters', {}, 'tools')}
          target={['claro_desktop_open_tool', {toolName: 'parameters'}]}
        />
      }

      {props.tools.map((tool) =>
        <Button
          key={tool.name}
          type={URL_BUTTON}
          className="list-group-item"
          icon={`fa fa-fw fa-${tool.icon}`}
          label={trans(tool.name, {}, 'tools')}
          target={tool.open}
        />
      )}
    </div>

    {0 !== props.actions.length &&
      <div className="app-current-user-footer">
        {props.actions.map(action =>
          <Button
            {...action}
            key={toKey(action.label)}
            className="app-current-user-btn"
            tooltip="bottom"
          />
        )}
      </div>
    }

  </div>

UserMenu.propTypes = {
  authenticated: T.bool.isRequired,
  tools: T.array.isRequired,
  actions: T.array.isRequired,
  login: T.string.isRequired,
  registration: T.string,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    publicUrl: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    roles: T.array
  }).isRequired
}

const HeaderUser = props =>
  <MenuButton
    id="authenticated-user-menu"
    className="app-header-user app-header-item app-header-btn"
    menu={
      <UserMenu
        authenticated={props.authenticated}
        currentUser={props.currentUser}
        login={props.login}
        registration={props.registration}
        tools={props.tools}
        actions={props.actions.filter(action => undefined === action.displayed || action.displayed)}
      />
    }
  >
    <UserAvatar picture={props.currentUser.picture} alt={false} />
    <span className="user-username hidden-xs">
      {props.authenticated ? props.currentUser.username : trans('login')}
    </span>
  </MenuButton>

HeaderUser.propTypes = {
  login: T.string.isRequired,
  tools: T.array,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  registration: T.string,
  authenticated: T.bool.isRequired,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    publicUrl: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    roles: T.array
  }).isRequired
}

HeaderUser.defaultProps = {
  tools: [],
  actions: []
}

export {
  HeaderUser
}
