import React from 'react'

import {NavLink} from '#/main/app/router'


const PlayerNav = props =>
  <nav className="tool-nav">
    {props.tabs.map((tab, tabIndex) =>
      <NavLink
        className="nav-tab"
        activeClassName="nav-tab-active"
        key={tabIndex}
        to={`/tab/${tab.id}`}
      >
        {tab.title}
      </NavLink>
    )}
  </nav>

export {
  PlayerNav
}
