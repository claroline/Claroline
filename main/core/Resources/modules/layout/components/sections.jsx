import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import Panel      from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {Action as ActionTypes} from '#/main/core/layout/action/prop-types'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'

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
      React.createElement('h'+props.level, {
        className: classes(props.displayLevel && `h${props.displayLevel}`,{
          opened: !props.disabled && props.expanded
        })
      }, [
        <span
          key="panel-icon"
          className={classes(props.icon, {
            'fa fa-fw fa-caret-down': !props.icon && !props.disabled && props.expanded,
            'fa fa-fw fa-caret-right': !props.icon && (props.disabled || !props.expanded)
          })}
          style={{marginRight: 10}}
        />,
        props.title,
        0 !== props.actions.length &&
        <div key="panel-actions" className="panel-actions">
          {props.actions.map((action, actionIndex) =>
            <TooltipAction
              {...action}
              key={`${props.id}-action-${actionIndex}`}
              id={`${props.id}-action-${actionIndex}`}
              disabled={!!action.disabled || props.disabled}
              className={classes({
                'btn-link-default': !action.primary && !action.dangerous,
                'btn-link-danger': action.dangerous,
                'btn-link-primary': action.primary
              })}
            />
          )}
        </div>
      ])
    }
  >
    {props.children}
  </Panel>
Section.propTypes = {
  className: T.string,
  id: T.string.isRequired,
  level: T.number.isRequired,
  displayLevel: T.number,
  icon: T.string,
  title: T.node.isRequired,
  expanded: T.bool,
  disabled: T.bool,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  children: T.node.isRequired
}

Section.defaultProps = {
  actions: [],
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
        key: child.props.id,
        eventKey: child.props.id,
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
