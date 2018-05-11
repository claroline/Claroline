import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import Panel      from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {toKey} from '#/main/core/scaffolding/text/utils'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {Heading} from '#/main/core/layout/components/heading'

/**
 * Renders a section.
 *
 * @param props
 * @constructor
 */
const Section = props =>
  <Panel
    {...omit(props, ['level', 'displayLevel', 'title', 'icon', 'actions', 'children'])}
    collapsible={true}
    expanded={props.disabled ? false : props.expanded}
    className={classes(props.className, {
      'panel-disabled': props.disabled
    })}
    header={
      <Heading
        level={props.level}
        displayLevel={props.displayLevel}
        className={classes({
          opened: !props.disabled && props.expanded
        })}
      >
        <span
          className={classes('icon-with-text-right', props.icon, {
            'fa fa-fw fa-caret-down': !props.icon && !props.disabled && props.expanded,
            'fa fa-fw fa-caret-right': !props.icon && (props.disabled || !props.expanded)
          })}
          aria-hidden={true}
        />

        {props.title}

        {0 !== props.actions.length &&
          <div className="panel-actions">
            {props.actions.map((action) =>
              <Button
                {...action}
                key={`${toKey(props.title)}-${toKey(action.label)}`}
                disabled={!!action.disabled || props.disabled}
                className="btn btn-link"
                tooltip="top"
              />
            )}
          </div>
        }
      </Heading>
    }
  >
    {props.children}
  </Panel>

Section.propTypes = {
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  icon: T.string,
  title: T.string.isRequired,
  expanded: T.bool,
  disabled: T.bool,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  children: T.node.isRequired
}

Section.defaultProps = {
  actions: [],
  level: 5,
  disabled: false
}

const Sections = props =>
  <PanelGroup
    className={props.className}
    accordion={props.accordion}
    defaultActiveKey={props.defaultOpened}
  >
    {React.Children.map(props.children, (child) => !child || 'hr' === child.type ? child :
      React.cloneElement(child, {
        key: child.props.id || toKey(child.props.title),
        eventKey: child.props.id || toKey(child.props.title),
        level: props.level,
        displayLevel: props.displayLevel
      })
    )}
  </PanelGroup>

Sections.propTypes = {
  className: T.string,
  accordion: T.bool,
  level: T.number, // level for panel headings
  displayLevel: T.number, // modifier for headings level (used when some headings levels are hidden in the page)
  defaultOpened: T.string,
  children: T.node.isRequired
}

Sections.defaultProps = {
  accordion: true,
  level: 5
}

export {
  Section,
  Sections
}
