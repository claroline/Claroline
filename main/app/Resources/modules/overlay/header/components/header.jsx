import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {HeaderBrand} from '#/main/app/overlay/header/components/brand'
import {HeaderLocale} from '#/main/app/overlay/header/components/locale'
import {HeaderNotifications} from '#/main/app/overlay/header/components/notifications'
import {HeaderTools} from '#/main/app/overlay/header/components/tools'
import {HeaderUser} from '#/main/app/overlay/header/components/user'
import {HeaderMain} from '#/main/app/overlay/header/components/main'

import {getWalkthrough} from '#/main/app/overlay/header/walkthroughs/menus'

const Header = props =>
  <header className="app-header">
    {((props.display.name && props.title) || props.logo) &&
      <HeaderBrand
        logo={props.logo}
        title={props.title}
        subtitle={props.subtitle}
        showTitle={props.display.name}
        redirectHome={props.redirectHome}
      />
    }

    {0 !== props.tools.length &&
      <HeaderTools
        type="tools"
        icon="fa fa-fw fa-tools"
        label={trans('tools')}
        tools={props.tools}
      />
    }

    <HeaderMain
      menu={props.mainMenu}
      authenticated={props.authenticated}
      currentContext={props.currentContext}
      user={props.currentUser}
    />

    {0 !== props.administration.length &&
      <HeaderTools
        type="administration"
        icon="fa fa-fw fa-cogs"
        label={trans('administration')}
        tools={props.administration}
        right={true}
      />
    }

    {props.authenticated &&
      <HeaderNotifications
        count={props.count}
        tools={props.notificationTools}
      />
    }

    <HeaderUser
      currentUser={props.currentUser}
      authenticated={props.authenticated}
      login={props.loginUrl}
      registration={props.registrationUrl}
      tools={props.userTools}
      actions={[
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-street-view',
          label: trans('show-walkthrough', {}, 'actions'),
          callback: () => props.startWalkthrough(getWalkthrough(props.tools, props.administration, props.authenticated, props.display))
        }, {
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-question',
          label: trans('show-help', {}, 'actions'),
          target: props.helpUrl,
          displayed: !!props.helpUrl
        }, { // todo : implement
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('show-info', {}, 'actions'),
          target: '#',
          displayed: false
        }, {
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-power-off',
          label: trans('logout'),
          target: ['claro_security_logout'],
          displayed: props.authenticated
        }
      ]}
    />

    {props.display.locale &&
      <HeaderLocale locale={props.locale} />
    }
  </header>

Header.propTypes = {
  mainMenu: T.string,

  /**
   * The current context of the app.
   */
  currentContext: T.shape({
    type: T.oneOf(['home', 'desktop', 'administration', 'workspace']).isRequired, // TODO : use constants
    data: T.object
  }).isRequired,

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
    locale: T.bool.isRequired,
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
  count: T.shape({
    notifications: T.number,
    messages: T.number
  }),
  authenticated: T.bool.isRequired,
  tools: T.array,
  userTools: T.array,
  notificationTools: T.array,
  administration: T.array,
  loginUrl: T.string.isRequired,
  helpUrl: T.string,
  registrationUrl: T.string,
  maintenance: T.bool,
  redirectHome: T.bool.isRequired,
  startWalkthrough: T.func.isRequired
}

Header.defaultProps = {
  isImpersonated: T.bool.isRequired,
  currentUser: null,
  tools: [],
  userTools: [],
  notificationTools: [],
  administration: [],
  registration: false,
  maintenance: false
}

export {
  Header
}
