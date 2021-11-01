import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {NavLink} from '#/main/app/router'
import {toKey} from '#/main/core/scaffolding/text'
import {Toolbar} from '#/main/app/action/components/toolbar'

const Vertical = (props) =>
  <nav
    {...omit(props, 'tabs', 'basePath')}
    className={classes('lateral-nav', props.className)}
  >
    {props.tabs
      .filter(tab => undefined === tab.displayed || tab.displayed)
      .map((tab) =>
        <NavLink
          to={props.basePath+tab.path}
          key={tab.id || toKey(tab.title)}
          className="lateral-link"
          exact={tab.exact}
        >
          {tab.icon &&
            <span className={classes(tab.icon, tab.title && 'icon-with-text-right')} />
          }
          {tab.title}

          {tab.actions && 0 !== tab.actions.length &&
            <Toolbar
              className="lateral-nav-actions"
              buttonName='btn btn-link'
              tooltip="right"
              actions={tab.actions}
            />
          }
        </NavLink>
      )
    }
  </nav>

Vertical.propTypes= {
  className: T.string,
  basePath: T.string,
  tabs: T.arrayOf(T.shape({
    id: T.string,
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string,
    title: T.node.isRequired,
    displayed: T.bool,
    actions: T.arrayOf(T.shape({
      // TODO : action types
    }))
  })).isRequired
}

Vertical.defaultProps = {
  basePath: ''
}

export {
  Vertical
}
