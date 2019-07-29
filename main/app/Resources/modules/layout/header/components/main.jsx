import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {HeaderBrand} from '#/main/app/layout/header/components/brand'
import {HeaderUser} from '#/main/app/layout/header/components/user'

import {getMenu} from '#/main/app/layout/header/utils'
import {getWalkthrough} from '#/main/app/layout/header/walkthroughs/menus'

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

      {props.menus.map((menu) => (
        <Await
          key={menu}
          for={getMenu(menu)}
          then={(menuApp) => createElement(menuApp.default, {
            authenticated: props.authenticated,
            user: props.currentUser
          })}
        />
      ))}

      <HeaderUser
        maintenance={props.maintenance}
        currentUser={props.currentUser}
        authenticated={props.authenticated}
        impersonated={props.impersonated}
        registration={props.registration}
        tools={props.tools}
        locale={props.locale}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-street-view',
            label: trans('walkthroughs'),
            callback: () => props.startWalkthrough(getWalkthrough(props.tools, props.administration, props.authenticated, props.display))
          }, {
            type: URL_BUTTON,
            icon: 'fa fa-fw fa-question',
            label: trans('help'),
            target: props.helpUrl,
            displayed: !!props.helpUrl
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
  maintenance: T.bool.isRequired,

  menus: T.arrayOf(T.string),
  locale: T.shape({
    current: T.string.isRequired,
    available: T.arrayOf(T.string).isRequired
  }).isRequired,
  logo: T.shape({
    url: T.string.isRequired,
    colorized: T.bool
  }),
  title: T.string,
  subtitle: T.string,
  display: T.shape({
    name: T.bool.isRequired
  }).isRequired,

  /**
   * The currently logged user.
   */
  currentUser: T.shape({
    id: T.string,
    name: T.string,
    username: T.string,
    publicUrl: T.string,
    picture: T.shape({
      url: T.string.isRequired
    }),
    roles: T.array
  }),
  impersonated: T.bool.isRequired,
  authenticated: T.bool.isRequired,
  tools: T.array,
  administration: T.array,
  helpUrl: T.string,
  registration: T.bool,
  startWalkthrough: T.func.isRequired,
  toggleMenu: T.func.isRequired
}

HeaderMain.defaultProps = {
  menus: [],
  impersonated: true,
  currentUser: null,
  tools: [],
  notificationTools: [],
  administration: [],
  registration: false
}

export {
  HeaderMain
}
