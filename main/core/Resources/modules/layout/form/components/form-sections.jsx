import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import Panel      from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

/**
 * Renders a form section.
 *
 * @param props
 * @constructor
 */
const FormSection = props =>
  <Panel
    {...omit(props, ['level', 'title', 'icon', 'children', 'validating', 'errors'])}

    header={
      React.createElement('h'+props.level, {
        className: classes({opened: props.expanded})
      }, [
        props.icon && <span key="panel-icon" className={props.icon} style={{marginRight: 10}} />,
        props.title
      ])
    }
  >
    {props.children}
  </Panel>

FormSection.propTypes = {
  id: T.string.isRequired,
  level: T.number,
  icon: T.string,
  title: T.string.isRequired,
  expanded: T.bool,
  children: T.node.isRequired,
  validating: T.bool,
  errors: T.object
}

const FormSections = props =>
  <PanelGroup
    accordion={props.accordion}
    defaultActiveKey={props.defaultOpened}
  >
    {React.Children.map(props.children, (child, index) =>
      React.cloneElement(child, {
        key: index,
        eventKey: index,
        level: props.level
      })
    )}
  </PanelGroup>

FormSections.propTypes = {
  accordion: T.bool,
  level: T.number, // level for panel headings
  defaultOpened: T.string,
  children: T.node.isRequired
}

FormSections.defaultProps = {
  accordion: true,
  level: 5
}

export {
  FormSection,
  FormSections
}
