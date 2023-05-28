import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import Accordion from 'react-bootstrap/Accordion'

import {toKey} from '#/main/core/scaffolding/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'

/**
 * Renders a section.
 */
const Section = (props) =>
  <Accordion.Item
    {...omit(props, ['level', 'title', 'subtitle', 'icon', 'actions', 'children', 'fill'])}
    className={props.className}
  >
    <Accordion.Header as={`h${props.level}`}>
      {(typeof props.icon === 'string') &&
        <span
          className={classes('icon-with-text-right', props.icon)}
          aria-hidden={true}
        />
      }

      {typeof props.icon !== 'string' && props.icon}

      <span role="presentation" className="flex-fill">
        {props.title}
        {props.subtitle &&
          <small>{props.subtitle}</small>
        }
      </span>

      {(props.status || 0 !== props.actions.length) &&
        <div className="panel-actions d-flex align-items-center my-n3">
          {props.status}

          {0 !== props.actions.length &&
            <Toolbar
              id={`${props.id || toKey(props.title)}-actions`}
              buttonName="btn btn-text-body text-reset me-3"
              tooltip="top"
              toolbar="more"
              actions={props.actions}
            />
          }
        </div>
      }
    </Accordion.Header>
    <Accordion.Body bsPrefix={props.fill ? 'accordion-body-flush' : undefined}>
      {props.children}
    </Accordion.Body>
  </Accordion.Item>

Section.propTypes = {
  id: T.string,
  className: T.string,
  level: T.number,
  icon: T.oneOfType([T.string, T.node]),
  title: T.string.isRequired,
  subtitle: T.string,
  fill: T.bool,
  status: T.element, // only used by FormSection to show validation. Maybe find better
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  children: T.node.isRequired
}

Section.defaultProps = {
  actions: [],
  level: 5,
  disabled: false,
  fill: false
}

const Sections = props =>
  <Accordion
    className={classes('sections', props.className)}
    alwaysOpen={!props.accordion}
    defaultActiveKey={props.defaultOpened}
    flush={props.flush}
  >
    {React.Children.map(props.children, (child) => child &&
      React.cloneElement(child, {
        key: child.props.id || toKey(child.props.title),
        eventKey: child.props.id || toKey(child.props.title),
        level: props.level
      })
    )}
  </Accordion>

Sections.propTypes = {
  className: T.string,
  flush: T.bool,
  accordion: T.bool,
  level: T.number, // level for panel headings
  defaultOpened: T.string,
  children: T.node.isRequired
}

Sections.defaultProps = {
  flush: false,
  accordion: true,
  level: 5
}

export {
  Section, // for retro-compatibility
  Sections, // for retro-compatibility

  Section as ContentSection,
  Sections as ContentSections
}
