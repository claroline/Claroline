import React from 'react'

import {NavLink} from '#/main/app/router'


const PlayerNav = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab, tabIndex) =>
      <NavLink
        className="nav-tab"
        activeClassName="nav-tab-active"
        exact={true}
        key={tabIndex}
        to={`/tab/${tab.id}`}
      >
        {tab.icon &&
          <span className={`fa fa-fw fa-${tab.icon} icon-with-text-right`} />
        }
        {tab.title}
      </NavLink>
    )}
  </nav>

export {
  PlayerNav
}
