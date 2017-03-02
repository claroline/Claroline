import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import {tex} from '../../utils/translate'
import Tab from 'react-bootstrap/lib/Tab'
import Nav from 'react-bootstrap/lib/Nav'
import Popover from 'react-bootstrap/lib/Popover'
import NavItem from 'react-bootstrap/lib/NavItem'
import {Feedback} from '../components/feedback-btn.jsx'
import {SolutionScore} from '../components/score.jsx'
import {utils} from './utils/utils'

/* global jsPlumb */

function getPopoverPosition(connectionClass, id){
  const containerRect =  document.getElementById('popover-container-' + id).getBoundingClientRect()
  const connectionRect =  document.querySelectorAll('.' + connectionClass)[0].getBoundingClientRect()
  // only compute top position
  return {
    top:  connectionRect.top + connectionRect.height / 2 - containerRect.top
  }
}

function initJsPlumb(jsPlumbInstance) {
  // defaults parameters for all connections
  jsPlumbInstance.importDefaults({
    Anchors: ['RightMiddle', 'LeftMiddle'],
    ConnectionsDetachable: false,
    Connector: 'Straight',
    HoverPaintStyle: {strokeStyle: '#FC0000'},
    LogEnabled: true,
    PaintStyle: {strokeStyle: '#777', lineWidth: 4}
  })

  jsPlumbInstance.registerConnectionTypes({
    'blue': {
      paintStyle     : { strokeStyle: '#31B0D5', lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: '#31B0D5',   lineWidth: 6 }
    },
    'green': {
      paintStyle     : { strokeStyle: '#5CB85C', lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: '#5CB85C',   lineWidth: 6 }
    },
    'red': {
      paintStyle     : { strokeStyle: '#D9534F', lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: '#D9534F',   lineWidth: 6 }
    },
    'default': {
      paintStyle     : { strokeStyle: 'grey',    lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: 'grey', lineWidth: 6 }
    }
  })
}

export const MatchLinkPopover = props =>
  <Popover
    id={`popover-${props.solution.firstId}-${props.solution.secondId}`}
    positionTop={props.top}
    placement="bottom"
    >
      <div className={classes(
        'fa',
        {'fa-check text-success' : props.solution.score > 0},
        {'fa-times text-danger' : props.solution.score <= 0 }
      )}>
      </div>
      &nbsp;<label className="label popover-label" dangerouslySetInnerHTML={{__html: props.solution.feedback}}/>
    &nbsp;<label className="label popover-label">{props.solution.score}</label>
  </Popover>


MatchLinkPopover.propTypes = {
  top: T.number.isRequired,
  solution: T.object.isRequired
}

class MatchItem extends Component{
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <div className={classes('item', this.props.type)} id={`${this.props.selectedTab}_${this.props.type}_${this.props.item.id}`}>
        <div className="item-content" dangerouslySetInnerHTML={{__html: this.props.item.data}} />
      </div>
    )
  }
}

MatchItem.propTypes = {
  type: T.string.isRequired,
  item: T.object.isRequired,
  selectedTab: T.string.isRequired
}

export class MatchPaper extends Component
{
  constructor(props) {
    super(props)
    this.state = {
      key: 'first',
      showPopover: false
    }
    this.handleSelect = this.handleSelect.bind(this)
    this.jsPlumbInstance = jsPlumb.getInstance()
    initJsPlumb(this.jsPlumbInstance)
    this.container = null
    this.handleConnectionClick = this.handleConnectionClick.bind(this)
    this.handleWindowResize = this.handleWindowResize.bind(this)
  }

  drawAnswers(){
    if (this.state.key === 'first') {
      for (const answer of this.props.answer) {
        const solution = this.props.item.solutions.find(solution => answer.firstId === solution.firstId && answer.secondId === solution.secondId)
        const connection = this.jsPlumbInstance.connect({
          source: 'first_source_' + answer.firstId,
          target: 'first_target_' + answer.secondId,
          type: solution && solution.score > 0 ? 'green' : 'red',
          deleteEndpointsOnDetach:true
        })

        const connectionClass = 'connection-' + answer.firstId + '-' + answer.secondId
        connection.addClass(connectionClass)

        connection.bind('click', (conn) => {
          this.handleConnectionClick(conn)
        })
      }
    } else {
      for (const solution of this.props.item.solutions) {
        if (solution.score > 0) {
          this.jsPlumbInstance.connect({
            source: 'second_source_' + solution.firstId,
            target: 'second_target_' + solution.secondId,
            type: 'blue',
            deleteEndpointsOnDetach:true
          })
        }
      }
    }
  }

  handleConnectionClick(connection) {
    const firstId = connection.sourceId.replace(`${this.state.key}_source_`, '')
    const secondId = connection.targetId.replace(`${this.state.key}_target_`, '')
    const connectionClass = 'connection-' + firstId + '-' + secondId
    const positions = getPopoverPosition(connectionClass, this.props.item.id)

    const solution = this.props.item.solutions.find(solution => solution.firstId === firstId && solution.secondId === secondId)
    if(this.state.showPopover) {
      this.setState({
        showPopover: false,
        top: 0,
        current: {}
      })
    } else {
      this.setState({
        showPopover: true,
        top: positions.top,
        current: solution ? solution : {firstId: firstId, secondId: secondId, score: 0}
      })
    }
  }

  handleWindowResize() {
    this.jsPlumbInstance.repaintEverything()
  }

  // switch tab handler
  handleSelect(key) {
    this.jsPlumbInstance.getConnections().forEach(conn => {
      this.jsPlumbInstance.detach(conn)
    })

    this.setState({key})
    window.setTimeout(() => {
      this.drawAnswers()
    }, 100)
  }

  componentDidMount() {
    this.jsPlumbInstance.setContainer(this.container)
    window.addEventListener('resize', this.handleWindowResize)
    // we have to wait for elements to be at there right place before drawing... so... timeout
    window.setTimeout(() => {
      this.drawAnswers()
    }, 200)
  }

  componentWillUnmount(){
    jsPlumb.detachEveryConnection()
    // use reset instead of deleteEveryEndpoint because reset also remove event listeners
    jsPlumb.reset()
    this.jsPlumbInstance = null
    delete this.jsPlumbInstance
  }

  render() {
    return (
      <Tab.Container id={`match-${this.props.item.id}-paper`} defaultActiveKey="first">
        <div>
            <Nav bsStyle="tabs">
              <NavItem eventKey="first" onSelect={() => this.handleSelect('first')}>
                  <span className="fa fa-user"></span> {tex('your_answer')}
              </NavItem>
              <NavItem eventKey="second" onSelect={() => this.handleSelect('second')}>
                <span className="fa fa-check"></span> {tex('expected_answer')}
              </NavItem>
            </Nav>
            <div ref={(el) => { this.container = el }} id={`jsplumb-container-${this.props.item.id}`} className="jsplumb-container" style={{position:'relative'}}>
              <Tab.Content animation>
                <Tab.Pane eventKey="first">
                  <span className="help-block">
                    <span className="fa fa-info-circle">&nbsp;</span>{tex('match_player_click_link_help')}
                  </span>
                  <div id={`match-question-paper-${this.props.item.id}-first`} className="match-question-paper">
                    <div className="jsplumb-row">
                      <div className="item-col">
                        <ul>
                        {this.props.item.firstSet.map((item) =>
                          <li key={'first_source_' + item.id}>
                            <MatchItem
                              item={item}
                              type="source"
                              selectedTab={this.state.key}
                            />
                          </li>
                        )}
                        </ul>
                      </div>
                      <div className="divide-col" id={`popover-container-${this.props.item.id}`}>
                        { this.state.showPopover &&
                            <MatchLinkPopover
                              top={this.state.top}
                              solution={this.state.current}
                            />
                          }
                      </div>
                      <div className="item-col">
                        <ul>
                        {this.props.item.secondSet.map((item) =>
                          <li key={'first_target_' + item.id}>
                            <MatchItem
                              item={item}
                              type="target"
                              selectedTab={this.state.key}
                            />
                          </li>
                        )}
                        </ul>
                      </div>
                    </div>
                  </div>
                </Tab.Pane>
                <Tab.Pane eventKey="second">
                  <span className="help-block" style={{visibility:'hidden'}} >
                    <span className="fa fa-info-circle">&nbsp;</span>{tex('match_player_click_link_help')}
                  </span>
                  <div id={`match-question-paper-${this.props.item.id}-second`} className="match-question-paper">
                    <div className="jsplumb-row">
                      <div className="item-col">
                        <ul>
                        {this.props.item.firstSet.map((item) =>
                          <li key={'second_source_' + item.id}>
                            <MatchItem
                              item={item}
                              type="source"
                              selectedTab={this.state.key}
                            />
                          </li>
                        )}
                        </ul>
                      </div>
                      <div className="divide-col" />
                      <div className="item-col">
                        <ul>
                        {this.props.item.secondSet.map((item) =>
                          <li key={'second_target_' + item.id}>
                            <MatchItem
                              item={item}
                              type="target"
                              selectedTab={this.state.key}
                            />
                          </li>
                        )}
                        </ul>
                      </div>
                    </div>
                    <div className="solution-row">
                      {this.props.item.solutions.map((solution) =>
                        <div
                          key={`solution-${solution.firstId}-${solution.secondId}`}
                          className={classes(
                            'item',
                            {'bg-info text-info' : solution.score > 0}
                          )}
                        >
                          <div className="sets">
                            <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionData(solution.firstId, this.props.item.firstSet)}} />
                            <span className="fa fa-chevron-left"></span>
                            <span className="fa fa-chevron-right"></span>
                            <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionData(solution.secondId, this.props.item.secondSet)}} />
                          </div>
                          <Feedback
                            id={`answer-${solution.firstId}-${solution.secondId}-feedback`}
                            feedback={solution.feedback}
                          />
                          <SolutionScore score={solution.score}/>
                        </div>
                      )}
                    </div>
                  </div>
                </Tab.Pane>
              </Tab.Content>
            </div>
          </div>
      </Tab.Container>
    )
  }
}

MatchPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    firstSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    secondSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.object),
    title: T.string,
    description: T.string
  }).isRequired,
  answer: T.array
}

MatchPaper.defaultProps = {
  answer: []
}
