import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import {useLocation} from 'react-router-dom'

import {LocaleFlag} from '#/main/app/intl/locale/components/flag'
import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Offcanvas} from '#/main/app/overlays/offcanvas'
import {Alert} from '#/main/app/components/alert'

import {MODAL_LOCALE} from '#/main/app/modals/locale'

import {getPlatformRoles} from '#/main/community/utils'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {Toolbar} from '#/main/app/action'
import {url} from '#/main/app/api'

const UserMenu = (props) => {
  const location = useLocation()

  return (
    <Offcanvas placement="end" show={props.show} onHide={props.closeMenu}>
      <Offcanvas.Header closeButton={true}>
        <Offcanvas.Title className="d-flex align-items-center flex-direction-row">
          {props.authenticated &&
            <UserAvatar className="me-3" size="sm" picture={props.currentUser.picture} alt={true} />
          }

          {!props.authenticated &&
            <span className="user-avatar user-avatar-sm fa fa-user-secret me-3" />
          }

          <h2 className="h5 mb-0">
            {props.currentUser.name}
            <small>{getPlatformRoles(props.currentUser.roles).map(role => trans(role.translationKey)).join(', ')}</small>
          </h2>
        </Offcanvas.Title>
      </Offcanvas.Header>

      <Offcanvas.Body>
        {props.unavailable &&
          <Alert type="danger" icon="fa fa-fw fa-power-off">
            {trans('platform_unavailable_alert', {}, 'administration')}
          </Alert>
        }

        {!props.authenticated &&
          <>
            {props.unavailable &&
              <p className="text-secondary">
                {trans('only_admin_login_help', {}, 'administration')}
              </p>
            }

            <Toolbar
              className="d-grid gap-1 mb-3"
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
                  displayed: !props.unavailable && props.registration
                }
              ]}
            />
          </>
        }

        {props.authenticated && props.impersonated &&
          <Alert type="warning" icon="fa fa-fw fa-mask">
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
          </Alert>
        }

        {props.authenticated && !get(props.currentUser, 'meta.mailValidated') &&
          <Alert type="warning" icon="fa fa-fw fa-at">
            {trans('email_not_validated', {email: props.currentUser.email})}
            <br/>
            {trans('email_not_validated_help')}

            <div className="btn-toolbar gap-1 mt-3 justify-content-end">
              <Button
                className="btn btn-warning"
                type={CALLBACK_BUTTON}
                label={trans('email_validation_send')}
                callback={props.sendValidationEmail}
                onClick={props.closeMenu}
              />
            </div>
          </Alert>
        }

        {!isEmpty(props.availableContexts) &&
          <Toolbar
            className="list-group"
            buttonName="list-group-item list-group-item-action"
            onClick={props.closeMenu}
            actions={props.availableContexts.map(context => ({
              name: context.name,
              type: LINK_BUTTON,
              icon: `fa fa-fw fa-${context.icon}`,
              label: trans(context.name, {}, 'context'),
              target: '/' + context.name
            }))}
          />
        }

        <Toolbar
          className="list-group mt-3"
          buttonName="list-group-item list-group-item-action"
          onClick={props.closeMenu}
          actions={[
            {
              name: 'locale',
              type: MODAL_BUTTON,
              icon: <LocaleFlag className="icon-with-text-right" locale={props.locale.current} />,
              label: trans(props.locale.current),
              modal: [MODAL_LOCALE, props.locale]
            }, {
              name: 'help',
              type: URL_BUTTON,
              icon: 'fa fa-fw fa-question',
              label: trans('help'),
              target: props.help,
              displayed: !!props.help
            }
          ]}
        />

        {props.authenticated &&
          <Toolbar
            className="list-group mt-3"
            buttonName="list-group-item list-group-item-action"
            onClick={props.closeMenu}
            actions={[
              {
                name: 'logout',
                type: URL_BUTTON,
                icon: 'fa fa-fw fa-power-off',
                label: trans('logout'),
                target: ['claro_security_logout'],
                displayed: props.authenticated
              }
            ]}
          />
        }
      </Offcanvas.Body>
    </Offcanvas>
  )
}

UserMenu.propTypes = {
  show: T.bool,
  className: T.string,
  unavailable: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  impersonated: T.bool.isRequired,
  availableContexts: T.array.isRequired,
  registration: T.bool,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    email: T.string,
    picture: T.string,
    meta: T.shape({
      mailValidated: T.bool
    }),
    roles: T.array
  }).isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  help: T.string,
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
      <>
        <Button
          id="app-user"
          className="app-header-user app-header-item app-header-btn"
          type={CALLBACK_BUTTON}
          icon={this.props.authenticated ?
            <UserAvatar picture={this.props.currentUser.picture} alt={true} /> :
            <span className="user-avatar fa fa-user-secret" />
          }
          label={this.props.authenticated ? this.props.currentUser.username : trans('login')}
          tooltip="bottom"
          callback={(e) => {
            this.setOpened(true)

            e.preventDefault()
            e.stopPropagation()

            e.target.blur()
          }}
          subscript={this.props.impersonated ? {
            type: 'text',
            status: 'danger',
            value: (<span className="fa fa-mask" />)
          } : undefined}
        />

        <UserMenu
          show={this.state.opened}
          unavailable={this.props.unavailable}
          authenticated={this.props.authenticated}
          impersonated={this.props.impersonated}
          availableContexts={this.props.availableContexts}
          currentUser={this.props.currentUser}
          registration={this.props.registration}
          locale={this.props.locale}
          help={this.props.help}
          closeMenu={() => this.setOpened(false)}
          sendValidationEmail={this.props.sendValidationEmail}
        />
      </>
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
  availableContexts: T.array.isRequired,
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    email: T.string,
    picture: T.string,
    meta: T.shape({
      mailValidated: T.bool
    }),
    roles: T.array
  }).isRequired,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  help: T.string,
  sendValidationEmail: T.func.isRequired
}

HeaderUser.defaultProps = {
  tools: [],
  actions: []
}

export {
  HeaderUser
}
