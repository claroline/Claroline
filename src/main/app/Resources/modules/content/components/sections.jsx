import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

// TODO : remove us
import Panel      from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {toKey} from '#/main/core/scaffolding/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {Heading} from '#/main/core/layout/components/heading'

/**
 * Renders a section.
 *
 * @param props
 * @constructor
 */
const Section = props =>
  <Panel
    {...omit(props, ['level', 'displayLevel', 'title', 'subtitle', 'icon', 'actions', 'children'])}
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
        {(!props.icon || typeof props.icon === 'string') &&
          <span
            className={classes('icon-with-text-right', props.icon, {
              'fa fa-fw fa-caret-down': !props.icon && !props.disabled && props.expanded,
              'fa fa-fw fa-caret-right': !props.icon && (props.disabled || !props.expanded)
            })}
            aria-hidden={true}
          />
        }

        {typeof props.icon !== 'string' && props.icon}

        {props.title}
        {props.subtitle &&
          <small>{props.subtitle}</small>
        }

        {(props.status || 0 !== props.actions.length) &&
          <div className="panel-actions">
            {props.status}

            {0 !== props.actions.length &&
              <Toolbar
                id={`${props.id || toKey(props.title)}-actions`}
                buttonName="btn btn-link"
                tooltip="top"
                toolbar="more"
                actions={props.actions}
              />
            }
          </div>
        }
      </Heading>
    }
  >
    {props.children}
  </Panel>

Section.propTypes = {
  id: T.string,
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  icon: T.oneOfType([T.string, T.node]),
  title: T.string.isRequired,
  subtitle: T.string,
  expanded: T.bool,
  disabled: T.bool,
  status: T.element, // only used by FormSection to show validation. Maybe find better
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
    className={classes('sections', props.className)}
    accordion={props.accordion}
    defaultActiveKey={props.defaultOpened}
  >
    {React.Children.map(props.children, (child) => child &&
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
  Section, // for retro-compatibility
  Sections, // for retro-compatibility

  Section as ContentSection,
  Sections as ContentSections
}
