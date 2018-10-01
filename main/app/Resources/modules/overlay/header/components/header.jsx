import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {OverlayStack} from '#/main/app/overlay/containers/stack'
import {WalkthroughOverlay} from '#/main/app/overlay/walkthrough/containers/overlay'

import {HeaderBrand} from '#/main/app/overlay/header/components/brand'
import {HeaderLocale} from '#/main/app/overlay/header/components/locale'
import {HeaderNotifications} from '#/main/app/overlay/header/components/notifications'
import {HeaderTitle} from '#/main/app/overlay/header/components/title'
import {HeaderTools} from '#/main/app/overlay/header/components/tools'
import {HeaderUser} from '#/main/app/overlay/header/components/user'
import {HeaderWorkspaces} from '#/main/app/overlay/header/components/workspaces'

function getWalkthrough(tools = [], administration = [], authenticated = false, display = {}) {
  const walkthrough = [
    // Intro
    {
      highlight: ['.app-header-container'],
      content: {
        title: trans('header.intro.title', {}, 'walkthrough'),
        message: trans('header.intro.message', {}, 'walkthrough')
      },
      position: {
        target: '.app-header-container',
        placement: 'bottom'
      }
    }
  ]

  // Tools
  if (0 !== tools.length) {
    walkthrough.push({
      highlight: ['#app-tools'],
      content: {
        title: trans('desktop_tools', {}, 'walkthrough'),
        message: trans('header.tools_group.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-tools',
        placement: 'bottom'
      },
      requiredInteraction: {
        type: 'click',
        target: '#app-tools',
        message: trans('header.tools_group.action', {}, 'walkthrough')
      }
    })

    // help for each tool
    tools.map(tool => walkthrough.push({
      highlight: [`#app-tools-${tool.name}`],
      content: {
        icon: `fa fa-${tool.icon}`,
        title: trans('tool', {toolName: trans(tool.name, {}, 'tools')}, 'walkthrough'),
        message: trans(`header.tools.${tool.name}.message`, {}, 'walkthrough'),
        link: trans(`header.tools.${tool.name}.documentation`, {}, 'walkthrough')
      },
      position: {
        target: `#app-tools-${tool.name}`,
        placement: 'right'
      }
    }))
  }

  // Workspaces
  walkthrough.push({
    highlight: ['#app-workspaces-menu'],
    content: {
      title: trans('header.workspaces_menu.title', {}, 'walkthrough'),
      message: trans('header.workspaces_menu.message', {}, 'walkthrough')
    },
    position: {
      target: '#app-workspaces-menu',
      placement: 'bottom'
    }/*,
     requiredInteraction: {
     type: 'click',
     target: '#app-workspaces-menu',
     message: trans('header.workspaces_menu.action', {}, 'walkthrough')
     }*/

  })

  // Administration
  if (0 !== administration.length) {
    walkthrough.push({
      highlight: ['#app-administration'],
      content: {
        title: trans('administration_tools', {}, 'walkthrough'),
        message: trans('header.administration_group.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-administration',
        placement: 'bottom'
      }/*,
      requiredInteraction: {
        type: 'click',
        target: '#app-administration',
        message: trans('header.administration_group.action', {}, 'walkthrough')
      }*/
    })

    // help for each tool
    // TODO : enable when
    /*administration.map(tool => walkthrough.push({
      highlight: [`#app-administration-${tool.name}`],
      content: {
        icon: `fa fa-${tool.icon}`,
        title: trans('tool', {toolName: trans(tool.name, {}, 'tools')}, 'walkthrough'),
        message: trans(`header.administration.${tool.name}.message`, {}, 'walkthrough'),
        link: trans(`header.administration.${tool.name}.documentation`, {}, 'walkthrough')
      },
      position: {
        target: `#app-administration-${tool.name}`,
        placement: 'left'
      }
    }))*/
  }

  if (authenticated) {
    // Notifications
    walkthrough.push({
      highlight: ['#app-notifications-menu'],
      content: {
        title: trans('header.notifications.title', {}, 'walkthrough'),
        message: trans('header.notifications.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-administration',
        placement: 'bottom'
      }/*,
       requiredInteraction: {
       type: 'click',
       target: '#app-notifications-menu',
       message: trans('header.app-notifications-menu.action', {}, 'walkthrough')
       }*/
    })

    // User menu
    walkthrough.push({
      highlight: ['#authenticated-user-menu'],
      content: {
        title: trans('header.user_menu.title', {}, 'walkthrough'),
        message: trans('header.user_menu.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-administration',
        placement: 'bottom'
      }/*,
      requiredInteraction: {
        type: 'click',
        target: '#authenticated-user-menu',
        message: trans('header.user_menu.action', {}, 'walkthrough')
      }*/
    })
  } else {
    // TODO : anonymous user menu doc
  }

  // Locale menu
  if (get(display, 'locale')) {
    walkthrough.push({
      highlight: ['#app-locale-select'],
      content: {
        message: trans('header.locale.message', {}, 'walkthrough')
      },
      position: {
        target: '#app-locale-select',
        placement: 'bottom'
      }
    })
  }

  return walkthrough
}

const Header = props =>
  <header className="app-header">
    {props.logo &&
      <HeaderBrand
        logo={props.logo}
        redirectHome={props.redirectHome}
      />
    }

    {(props.display.name && props.title) &&
      <HeaderTitle
        title={props.title}
        subtitle={props.subtitle}
        redirectHome={props.redirectHome}
      />
    }

    {0 !== props.tools.length &&
      <HeaderTools
        type="tools"
        icon="fa fa-fw fa-wrench"
        label={trans('tools')}
        tools={props.tools}
      />
    }

    <HeaderWorkspaces
      {...props.workspaces}
      label={trans('history')}
      currentLocation={props.currentLocation}
      currentUser={props.currentUser}
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
      currentLocation={props.currentLocation}
    />

    {props.display.locale &&
      <HeaderLocale locale={props.locale} />
    }

    <OverlayStack>
      <WalkthroughOverlay />
    </OverlayStack>
  </header>

Header.propTypes = {
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
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  count: T.shape({
    notifications: T.number,
    messages: T.number
  }),
  authenticated: T.bool.isRequired,
  currentLocation: T.string.isRequired,
  tools: T.array,
  userTools: T.array,
  administration: T.array,

  workspaces: T.shape({
    personal: T.shape({

    }),
    current: T.shape({

    }),
    history: T.arrayOf(T.shape({

    }))
  }).isRequired,

  loginUrl: T.string.isRequired,
  helpUrl: T.string,
  registrationUrl: T.string,
  maintenance: T.bool,
  redirectHome: T.bool.isRequired
}

Header.defaultProps = {
  currentUser: null,
  tools: [],
  userTools: [],
  administration: [],
  registration: false,
  maintenance: false
}

export {
  Header
}
