import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import Panel      from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

class FormSections extends Component {
  constructor(props) {
    super(props)

    this.state = {
      openedPanel: null
    }
  }

  makeSection(section) {
    return (
      <Panel
        key={section.id}
        eventKey={section.id}
        header={
          React.createElement('h'+this.props.level, {
            className: classes('panel-title', {opened: section.id === this.state.openedPanel})
          }, [
            section.icon && <span key="panel-icon" className={section.icon} style={{marginRight: 10}} />,
            section.label
          ])
        }
      >
        {section.children}
      </Panel>
    )
  }

  render() {
    return (
      <PanelGroup
        accordion={this.props.accordion}
        activeKey={this.state.openedPanel}
        onSelect={(activeKey) => this.setState({openedPanel: activeKey !== this.state.openedPanel ? activeKey : null})}
      >
        {this.props.sections.map(
          section => this.makeSection(section)
        )}
      </PanelGroup>
    )
  }
}

FormSections.propTypes = {
  accordion: T.bool,
  level: T.number, // level for panel headings
  sections: T.arrayOf(T.shape({
    id: T.string.isRequired,
    icon: T.string,
    label: T.string.isRequired,
    children: T.node.isRequired
  })).isRequired
}

FormSections.defaultProps = {
  accordion: true,
  level: 5
}

export {
  FormSections
}
