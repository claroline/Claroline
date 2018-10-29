import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'

import {getMenu} from '#/main/app/overlay/header/utils'

/**
 * The main menu of the Header.
 * It is provided by the platform configuration and a plugin.
 */
class HeaderMain extends Component {
  constructor(props) {
    super(props)

    this.state = {menu: null}
  }

  render() {
    return (
      <div className="app-header-main">
        {this.props.menu &&
          <Await
            for={getMenu(this.props.menu)}
            then={(menu) => this.setState({menu: menu.default})}
            placeholder={
              <span className="fa fa-fw fa-spinner fa-spin" />
            }
          >
            {this.state.menu && React.createElement(this.state.menu, {
              context: this.props.context,
              authenticated: this.props.authenticated,
              user: this.props.user
            })}
          </Await>
        }
      </div>
    )
  }
}

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
