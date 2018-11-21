import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'

import {getMenu} from '#/main/app/overlay/header/utils'

/**
 * The main menu of the Header.
 * It is provided by the platform configuration and a plugin.
 */
const HeaderMain = (props) =>
  <div className="app-header-main">
    {props.menu &&
      <Await
        for={getMenu(props.menu)}
        then={(menu) => React.createElement(menu.default, {
          context: props.context,
          authenticated: props.authenticated,
          user: props.user
        })}
        placeholder={
          <span className="fa fa-fw fa-spinner fa-spin" />
        }
      />
    }
  </div>

HeaderMain.propTypes = {
  menu: T.string,
  context: T.shape({
    type: T.oneOf(['home', 'desktop', 'administration', 'workspace']).isRequired, // TODO : use constants
    data: T.shape({
      name: T.string.isRequired
    })
  }).isRequired,
  authenticated: T.bool.isRequired,
  user: T.object // if no user authenticated, it contains a placeholder object
}

export {
  HeaderMain
}
