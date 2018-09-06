import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {URL_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu/components/button'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {UserMicro} from '#/main/core/user/components/micro'
import {constants as roleConstants} from '#/main/core/user/role/constants'
import {User as UserTypes} from '#/main/core/user/prop-types'

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
          target={['claro_user_profile', {publicUrl: props.currentUser.publicUrl}]}
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

      {props.userTools &&
        props.userTools.map((tool, index) =>
          <Button
            key ={index}
            type={URL_BUTTON}
            className="list-group-item"
            icon={`fa fa-fw fa-${tool.icon}`}
            label={trans(tool.name, {}, 'tools')}
            target={tool.open}
          />
        )
      }
    </div>

    <div className="app-current-user-footer">
      {props.help &&
        <Button
          type={URL_BUTTON}
          className="app-current-user-btn"
          icon="fa fa-fw fa-question"
          label={trans('help')}
          tooltip="bottom"
          target={props.help}
        />
      }

      {/* <Button
        type={URL_BUTTON}
        className="app-current-user-btn"
        icon="fa fa-fw fa-info"
        label={trans('about')}
        tooltip="bottom"
        target=""
      /> */}

      {props.authenticated &&
        <Button
          type={URL_BUTTON}
          className="app-current-user-btn"
          icon="fa fa-fw fa-power-off"
          label={trans('logout')}
          tooltip="bottom"
          target={['claro_security_logout']}
        />
      }
    </div>

  </div>

UserMenu.propTypes = {
  authenticated: T.bool.isRequired,
  userTools: T.array,
  login: T.string.isRequired,
  registration: T.string,
  help: T.string,
  currentUser: T.shape(UserTypes.propTypes).isRequired
}

const HeaderUser = props =>
  <MenuButton
    id="authenticated-user-menu"
    className="app-header-item app-header-btn"
    menu={
      <UserMenu
        authenticated={props.authenticated}
        currentUser={props.currentUser}
        login={props.login}
        registration={props.registration}
        help={props.help}
        userTools={props.userTools}
      />
    }
  >
    <UserMicro {...props.currentUser} showUsername={true} />
  </MenuButton>

HeaderUser.propTypes = {
  login: T.string.isRequired,
  userTools: T.array,
  registration: T.string,
  help: T.string,
  authenticated: T.bool.isRequired,
  currentUser: T.shape({

  }).isRequired
}

export {
  HeaderUser
}
