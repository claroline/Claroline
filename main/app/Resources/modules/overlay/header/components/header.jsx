import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {HeaderBrand} from '#/main/app/overlay/header/components/brand'
import {HeaderLocale} from '#/main/app/overlay/header/components/locale'
import {HeaderNotifications} from '#/main/app/overlay/header/components/notifications'
import {HeaderTitle} from '#/main/app/overlay/header/components/title'
import {HeaderTools} from '#/main/app/overlay/header/components/tools'
import {HeaderUser} from '#/main/app/overlay/header/components/user'
import {HeaderWorkspaces} from '#/main/app/overlay/header/components/workspaces'

const Header = props =>
  <header className="app-header">
    {props.logo &&
      <HeaderBrand
        logo={props.logo}
      />
    }

    {props.title &&
      <HeaderTitle
        title={props.title}
        subtitle={props.subtitle}
      />
    }

    {0 !== props.tools.length &&
      <HeaderTools
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
        icon="fa fa-fw fa-cogs"
        label={trans('administration')}
        tools={props.administration}
        right={true}
      />
    }

    {'Invité' !== props.currentUser.name &&
      <HeaderNotifications
        count={props.count}
      />
    }

    <HeaderUser
      currentUser={props.currentUser}
      authenticated={props.authenticated}
      login={props.loginUrl}
      help={props.helpUrl}
      registration={props.registrationUrl}
      userTools={props.userTools}
      currentLocation={props.currentLocation}
    />

    {props.display.locale &&
      <HeaderLocale locale={props.locale} />
    }
  </header>

//

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
    locale: T.bool.isRequired
  }).isRequired,

  /**
   * The currently logged user.
   */
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  count: T.number,
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
  maintenance: T.bool
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
