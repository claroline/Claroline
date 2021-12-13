import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'
import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MENU_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentHelp} from '#/main/app/content/components/help'

import {MODAL_LOCALE} from '#/main/app/modals/locale'

import {getPlatformRoles} from '#/main/core/user/utils'
import {UserAvatar} from '#/main/core/user/components/avatar'

const UserMenu = props =>
  <div className="app-header-dropdown app-current-user dropdown-menu dropdown-menu-right">
    <div className="app-header-dropdown-header">
      <div className="app-header-dropdown-icon">
        {props.authenticated &&
          <UserAvatar picture={props.currentUser.picture} alt={true} />
        }

        {!props.authenticated &&
          <span className="user-avatar fa fa-user-secret" />
        }
      </div>

      <h2 className="h4">
        {props.currentUser.name}
      </h2>

      <em>
        {getPlatformRoles(props.currentUser.roles).join(', ')}
      </em>
    </div>

    {props.unavailable &&
      <div className="alert alert-danger">
        <span className="fa fa-fw fa-power-off icon-with-text-right" />
        {trans('platform_unavailable_alert', {}, 'administration')}
      </div>
    }

    {props.authenticated && props.impersonated &&
      <div className="alert alert-warning">
        <span className="fa fa-fw fa-mask icon-with-text-right" />
        {trans('impersonation_mode_alert')}
      </div>
    }

    {props.authenticated && !get(props.currentUser, 'meta.mailValidated') &&
      <div className="alert alert-warning">
        <div>
          {trans('email_not_validated', {email: props.currentUser.email})}
          {trans('email_not_validated_help')}
          {trans('email_not_validated_send')}
        </div>
        <Button
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-envelope"
          label={trans('email_validation_send')}
          callback={() => props.sendValidationEmail()}
          tooltip="bottom"
        />
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
          onClick={props.closeMenu}
          active={false}
        />

        {props.unavailable &&
          <ContentHelp help={trans('only_admin_login_help', {}, 'administration')} />
        }

        {!props.unavailable && props.registration &&
          <Button
            type={LINK_BUTTON}
            className="btn btn-block"
            label={trans('create-account', {}, 'actions')}
            target="/registration"
            onClick={props.closeMenu}
            active={false}
          />
        }
      </div>
    }

    {props.authenticated &&
      <div className="app-current-user-tools list-group">
        <Button
          type={LINK_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-atlas"
          label={trans('desktop')}
          target="/desktop"
          exact={true}
          onClick={props.closeMenu}
        />

        <Button
          type={LINK_BUTTON}
          className="list-group-item"
          icon="fa fa-fw fa-user"
          label={trans('my_account')}
          target="/account"
          onClick={props.closeMenu}
        />

        {props.administration &&
          <Button
            type={LINK_BUTTON}
            className="list-group-item"
            icon="fa fa-fw fa-cogs"
            label={trans('administration')}
            target="/admin"
            onClick={props.closeMenu}
          />
        }
      </div>
    }

    <div className="app-current-user-footer">
      <Button
        className="app-current-locale app-current-user-btn btn-link"
        type={MODAL_BUTTON}
        modal={[MODAL_LOCALE, props.locale]}
        icon={<LocaleFlag locale={props.locale.current} />}
        label={trans(props.locale.current)}
        onClick={props.closeMenu}
      />

      {props.actions.map(action =>
        <Button
          {...action}
          key={toKey(action.label)}
          className="app-current-user-btn btn-link"
          tooltip="bottom"
          onClick={props.closeMenu}
        />
      )}
    </div>
  </div>

UserMenu.propTypes = {
  unavailable: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  impersonated: T.bool.isRequired,
  administration: T.bool.isRequired,
  actions: T.array.isRequired,
  registration: T.bool,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    email: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    meta: T.shape({
      mailValidated: T.bool
    }),
    roles: T.array
  }).isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  closeMenu: T.func.isRequired,
  sendValidationEmail: T.func.isRequired
}

class HeaderUser extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false
    }

    this.setOpened = this.setOpened.bind(this)
  }

  setOpened(opened) {
    this.setState({opened: opened})
  }

  render() {
    return (
      <Button
        id="app-user"
        className="app-header-user app-header-item app-header-btn"
        type={MENU_BUTTON}
        icon={this.props.authenticated ?
          <UserAvatar picture={this.props.currentUser.picture} alt={true} /> :
          <span className="user-avatar fa fa-user-secret" />
        }
        label={this.props.authenticated ? this.props.currentUser.username : trans('login')}
        tooltip="bottom"
        opened={this.state.opened}
        onToggle={this.setOpened}
        subscript={this.props.impersonated ? {
          type: 'text',
          status: 'danger',
          value: (<span className="fa fa-mask" />)
        } : undefined}
        menu={
          <UserMenu
            unavailable={this.props.unavailable}
            authenticated={this.props.authenticated}
            impersonated={this.props.impersonated}
            administration={this.props.administration}
            currentUser={this.props.currentUser}
            registration={this.props.registration}
            locale={this.props.locale}
            actions={this.props.actions.filter(action => undefined === action.displayed || action.displayed)}
            closeMenu={() => this.setOpened(false)}
            sendValidationEmail={this.props.sendValidationEmail}
          />
        }
      />
    )
  }
}

HeaderUser.propTypes = {
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  registration: T.bool,
  unavailable: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  impersonated: T.bool.isRequired,
  administration: T.bool.isRequired,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    email: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    meta: T.shape({
      mailValidated: T.bool
    }),
    roles: T.array
  }).isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  sendValidationEmail: T.func.isRequired
}

HeaderUser.defaultProps = {
  tools: [],
  actions: []
}

export {
  HeaderUser
}
