import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'
import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {MODAL_LOCALE} from '#/main/app/modals/locale'

import {UserAvatar} from '#/main/core/user/components/avatar'
import {constants as roleConstants} from '#/main/core/user/role/constants'

// TODO : add email validation warning
// TODO : add user poster when available

const UserMenu = props =>
  <div className="app-header-dropdown app-current-user dropdown-menu dropdown-menu-right">
    <div className="app-header-dropdown-header">
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

    {props.maintenance &&
      <div className="alert alert-warning">
        <span className="fa fa-fw fa-exclamation-triangle" />
        {trans('maintenance_mode_alert')}
      </div>
    }

    {props.impersonated &&
      <div className="alert alert-warning">
        <span className="fa fa-fw fa-mask" />
        {trans('impersonation_mode_alert')}
      </div>
    }

    {!props.authenticated &&
      <div className="app-current-user-body">
        <Button
          type={LINK_BUTTON}
          className="btn btn-block btn-emphasis"
          label={trans('login', {}, 'actions')}
          primary={true}
          target="/login"
        />

        {props.registration &&
          <Button
            type={LINK_BUTTON}
            className="btn btn-block"
            label={trans('self-register', {}, 'actions')}
            target="/registration"
          />
        }
      </div>
    }

    <div className="app-current-user-tools list-group">
      {props.authenticated &&
        <Button
          type={LINK_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-atlas"
          label={trans('desktop')}
          target="/desktop"
          exact={true}
        />
      }

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
          type={LINK_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-cog"
          label={trans('parameters', {}, 'tools')}
          target={'/desktop/parameters'}
        />
      }

      {props.authenticated &&
        <Button
          type={LINK_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-cogs"
          label={trans('administration')}
          target="/admin"
        />
      }

      {props.tools.map((tool) =>
        <Button
          key={tool.name}
          type={LINK_BUTTON}
          className="list-group-item"
          icon={`fa fa-fw fa-${tool.icon}`}
          label={trans(tool.name, {}, 'tools')}
          target={`/desktop/${tool.name}`}
        />
      )}
    </div>

    <div className="app-current-user-footer">
      <Button
        className="app-current-locale app-current-user-btn btn-link"
        type={MODAL_BUTTON}
        modal={[MODAL_LOCALE, props.locale]}
        icon={<LocaleFlag locale={props.locale.current} />}
        label={trans(props.locale.current)}
      />

      {props.actions.map(action =>
        <Button
          {...action}
          key={toKey(action.label)}
          className="app-current-user-btn btn-link"
          tooltip="bottom"
        />
      )}
    </div>

  </div>

UserMenu.propTypes = {
  maintenance: T.bool,
  authenticated: T.bool.isRequired,
  impersonated: T.bool.isRequired,
  tools: T.array.isRequired,
  actions: T.array.isRequired,
  registration: T.bool,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    publicUrl: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    roles: T.array
  }).isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired
}

const HeaderUser = props =>
  <Button
    id="app-user"
    className="app-header-user app-header-item app-header-btn"
    type={MENU_BUTTON}
    icon={
      <UserAvatar picture={props.currentUser.picture} alt={false} />
    }
    label={props.authenticated ? props.currentUser.username : trans('login')}
    tooltip="bottom"
    subscript={props.impersonated ? {
      type: 'text',
      status: 'danger',
      value: (<span className="fa fa-mask" />)
    } : undefined}
    menu={
      <UserMenu
        authenticated={props.authenticated}
        impersonated={props.impersonated}
        currentUser={props.currentUser}
        registration={props.registration}
        tools={props.tools}
        locale={props.locale}
        actions={props.actions.filter(action => undefined === action.displayed || action.displayed)}
      />
    }
  />

HeaderUser.propTypes = {
  tools: T.array,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  registration: T.bool,
  maintenance: T.bool,
  authenticated: T.bool.isRequired,
  impersonated: T.bool.isRequired,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    publicUrl: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    roles: T.array
  }).isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired
}

HeaderUser.defaultProps = {
  tools: [],
  actions: []
}

export {
  HeaderUser
}
