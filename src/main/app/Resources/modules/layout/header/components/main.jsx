import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {HeaderBrand} from '#/main/app/layout/header/components/brand'
import {HeaderUser} from '#/main/app/layout/header/components/user'

import {getMenu} from '#/main/app/layout/header/utils'

const HeaderMain = props =>
  <header className="app-header-container">
    <div className="app-header" role="presentation">
      <Button
        className="app-header-item app-header-btn"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-bars"
        label={trans('menu')}
        tooltip="bottom"
        callback={props.toggleMenu}
      />

      {((props.display.name && props.title) || props.logo) &&
        <HeaderBrand
          logo={props.logo}
          title={props.title}
          subtitle={props.subtitle}
          showTitle={props.display.name}
        />
      }

      {!props.unavailable && Object.keys(props.menus)
        .filter(menuName => undefined === props.menus[menuName].displayed || props.menus[menuName].displayed)
        .sort((a, b) => props.menus[a].order - props.menus[b].order)
        .map((menuName) => (
          <Await
            key={menuName}
            for={getMenu(menuName)}
            then={(menuApp) => createElement(menuApp.default.component, {
              authenticated: props.authenticated,
              user: props.currentUser,
              parameters: props.menus[menuName]
            })}
          />
        ))
      }

      <HeaderUser
        unavailable={props.unavailable}
        currentUser={props.currentUser}
        authenticated={props.authenticated}
        impersonated={props.impersonated}
        administration={props.administration}
        registration={props.registration}
        locale={props.locale}
        sendValidationEmail={props.sendValidationEmail}
        actions={[
          {
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-question',
            label: trans('help'),
            target: props.helpUrl,
            displayed: props.display.help && !!props.helpUrl
          }, {
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-power-off',
            label: trans('logout'),
            target: ['claro_security_logout'],
            displayed: props.authenticated && !props.impersonated,
            dangerous: true
          }, {
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-power-off',
            label: trans('logout'),
            target: ['claro_index', {_switch: '_exit'}],
            displayed: props.impersonated,
            dangerous: true
          }
        ]}
      />
    </div>
  </header>

HeaderMain.propTypes = {
  unavailable: T.bool.isRequired,

  menus: T.object,
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  logo: T.string,
  title: T.string,
  subtitle: T.string,
  display: T.shape({
    name: T.bool.isRequired,
    about: T.bool.isRequired,
    help: T.bool.isRequired
  }).isRequired,

  /**
   * The currently logged user.
   */
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    roles: T.array
  }),
  impersonated: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  administration: T.bool.isRequired,
  helpUrl: T.string,
  registration: T.bool,
  sendValidationEmail: T.func.isRequired,
  toggleMenu: T.func.isRequired
}

HeaderMain.defaultProps = {
  menus: {},
  impersonated: true,
  administration: false,
  currentUser: null,
  notificationTools: [],
  registration: false
}

export {
  HeaderMain
}
