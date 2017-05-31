import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Tab from 'react-bootstrap/lib/Tab'
import Nav from 'react-bootstrap/lib/Nav'
import NavItem from 'react-bootstrap/lib/NavItem'

import {tex} from '#/main/core/translation'

export class PaperTabs extends Component {
  constructor(props) {
    super(props)
    this.handleSelect = this.handleSelect.bind(this)
  }

  handleSelect(key) {
    if(this.props.onTabChange) {
      this.props.onTabChange(key)
    }
  }

  render() {
    return (
      <Tab.Container id={`${this.props.id}-paper`} defaultActiveKey="first">
        <div>
          <Nav bsStyle="tabs">
            <NavItem eventKey="first" onSelect={() => this.handleSelect('first')}>
              <span className="fa fa-fw fa-user"></span> {tex('your_answer')}
            </NavItem>
            {!this.props.hideExpected &&
              <NavItem eventKey="second" onSelect={() => this.handleSelect('second')}>
                <span className="fa fa-fw fa-check"></span> {tex('expected_answer')}
              </NavItem>
            }
          </Nav>

          <Tab.Content animation>
            <Tab.Pane eventKey="first">
              {this.props.yours}
            </Tab.Pane>
            {!this.props.hideExpected &&
              <Tab.Pane eventKey="second">
                {this.props.expected}
              </Tab.Pane>
            }
          </Tab.Content>
        </div>
      </Tab.Container>
    )
  }
}

PaperTabs.propTypes = {
  id: T.string.isRequired,
  yours: T.object.isRequired,
  expected: T.object,
  onTabChange: T.func,
  hideExpected: T.bool
}
