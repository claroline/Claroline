import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {NavLink} from '#/main/app/router'


const Vertical = (props) =>
  <nav className="lateral-nav">
    {props.tabs.map((tab, tabIndex) =>
      <NavLink
        to={tab.path}
        key={`tab-link-${tabIndex}`}
        className="lateral-link"
        exact={tab.exact}
      >
        {tab.icon &&
          <span className={classes(tab.icon, tab.title && 'icon-with-text-right')} />
        }
        {tab.title}
      </NavLink>
    )}
  </nav>

Vertical.propTypes= {
  tabs: T.arrayOf(T.shape({
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string.isRequired,
    title: T.string.isRequired
  })).isRequired
}


export {
  Vertical
}
