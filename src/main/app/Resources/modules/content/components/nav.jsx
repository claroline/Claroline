import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {Button, Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {toKey} from '#/main/core/scaffolding/text'

const ContentNav = (props) =>
  <nav role="navigation" className={props.className}>
    <ul
      {...omit(props, 'className', 'type', 'sections', 'path')}
      className={classes('nav nav-pills', {
        'flex-column': 'vertical' === props.type,
        'nav-justified': 'vertical' !== props.type
      })}
    >
      {props.sections
        .filter(section => undefined === section.displayed || section.displayed)
        .map((section) =>
          <li className="nav-item" key={section.id || toKey(section.title)}>
            <Button
              {...omit(section, 'title', 'displayed', 'path', 'actions')}
              className="nav-link"
              type={LINK_BUTTON}
              label={section.title}
              target={props.path+section.path}
            >
              {section.actions && 0 !== section.actions.length &&
                <Toolbar
                  className="ms-auto"
                  buttonName="btn btn-link p-0"
                  tooltip="right"
                  actions={section.actions}
                />
              }
            </Button>
          </li>
        )
      }
    </ul>
  </nav>

ContentNav.propTypes= {
  className: T.string,
  type: T.oneOf(['vertical', 'horizontal']).isRequired,
  path: T.string,
  sections: T.arrayOf(T.shape({
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

export {
  ContentNav
}
