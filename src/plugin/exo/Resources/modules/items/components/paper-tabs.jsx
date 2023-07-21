import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import Tab from 'react-bootstrap/Tab'
import Nav from 'react-bootstrap/Nav'
import NavItem from 'react-bootstrap/NavItem'

import {trans} from '#/main/app/intl/translation'

export class PaperTabs extends Component {
  constructor(props) {
    super(props)
    this.handleSelect = this.handleSelect.bind(this)
    this.defaultKey = 'first'
    if (!props.showExpected && !props.showYours && props.showStats) {
      this.defaultKey = 'third'
    }
  }

  handleSelect(key) {
    if (this.props.onTabChange) {
      this.props.onTabChange(key)
    }
  }

  render() {
    return (
      <Tab.Container id={`${this.props.id}-paper`} defaultActiveKey={this.defaultKey}>
        <div>
          <Nav bsStyle="tabs">
            {this.props.showYours &&
              <NavItem eventKey="first" onSelect={() => this.handleSelect('first')}>
                <span className="fa fa-fw fa-user" /> {trans('your_answer', {}, 'quiz')}
              </NavItem>
            }
            {this.props.showExpected &&
              <NavItem eventKey="second" onSelect={() => this.handleSelect('second')}>
                <span className="fa fa-fw fa-check" /> {trans('expected_answer', {}, 'quiz')}
              </NavItem>
            }
            {this.props.showStats &&
              <NavItem eventKey="third" onSelect={() => this.handleSelect('third')}>
                <span className="fa fa-fw fa-bar-chart" /> {trans('stats', {}, 'quiz')}
              </NavItem>
            }
          </Nav>

          <Tab.Content animation>
            {this.props.showYours &&
              <Tab.Pane eventKey="first">
                {this.props.yours}
              </Tab.Pane>
            }
            {this.props.showExpected &&
              <Tab.Pane eventKey="second">
                {this.props.expected}
              </Tab.Pane>
            }
            {this.props.showStats &&
              <Tab.Pane eventKey="third">
                {this.props.stats}
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
  stats: T.object,
  onTabChange: T.func,
  showExpected: T.bool,
  showStats: T.bool,
  showYours: T.bool
}

PaperTabs.defaultProps = {
  showYours: false
}
