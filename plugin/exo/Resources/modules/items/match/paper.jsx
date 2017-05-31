import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {tex} from '#/main/core/translation'
import Tab from 'react-bootstrap/lib/Tab'
import Nav from 'react-bootstrap/lib/Nav'
import Popover from 'react-bootstrap/lib/Popover'
import NavItem from 'react-bootstrap/lib/NavItem'
import {Feedback} from '../components/feedback-btn.jsx'
import {SolutionScore} from '../components/score.jsx'
import {utils} from './utils/utils'

function getPopoverPosition(connectionClass, id){
  const containerRect =  document.getElementById('popover-container-' + id).getBoundingClientRect()
  const connectionRect =  document.querySelectorAll('.' + connectionClass)[0].getBoundingClientRect()
  // only compute top position
  return {
    top:  connectionRect.top + connectionRect.height / 2 - containerRect.top
  }
}

export const MatchLinkPopover = props =>
  <Popover
    id={`popover-${props.solution.firstId}-${props.solution.secondId}`}
    positionTop={props.top}
    placement="bottom"
  >
    <div className={classes(
      'fa fa-fw',
      {'fa-check text-success' : props.solution.score > 0},
      {'fa-times text-danger' : props.solution.score <= 0 }
    )}>
    </div>
    {props.showScore &&
      <SolutionScore score={props.solution.score} />
    }

    {props.solution.feedback &&
      <div className="match-association-feedback" dangerouslySetInnerHTML={{__html: props.solution.feedback}}/>
    }
  </Popover>

MatchLinkPopover.propTypes = {
  top: T.number.isRequired,
  solution: T.object.isRequired,
  showScore: T.bool.isRequired
}

const MatchItem = props =>
  <div
    id={`${props.selectedTab}_${props.type}_${props.item.id}`}
    className={classes('match-item answer-item', props.type)}
    dangerouslySetInnerHTML={{__html: props.item.data}}
  />

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
    this.jsPlumbInstance = utils.getJsPlumbInstance(false)
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
          type: solution && solution.score > 0 ? 'correct' : 'incorrect',
          deleteEndpointsOnDetach: true
        })

        const connectionClass = 'connection-' + answer.firstId + '-' + answer.secondId
        if (connection) {
          connection.addClass(connectionClass)

          connection.bind('click', (conn) => {
            this.handleConnectionClick(conn)
          })
        }
      }
    } else {
      for (const solution of this.props.item.solutions) {
        if (solution.score > 0) {
          this.jsPlumbInstance.connect({
            source: 'second_source_' + solution.firstId,
            target: 'second_target_' + solution.secondId,
            type: 'selected',
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
    utils.resetJsPlumb()

    this.jsPlumbInstance = null
    delete this.jsPlumbInstance
  }

  render() {
    return (
      <Tab.Container id={`match-${this.props.item.id}-paper`} defaultActiveKey="first">
        <div className="match-paper">
            <Nav bsStyle="tabs">
              <NavItem eventKey="first" onSelect={() => this.handleSelect('first')}>
                  <span className="fa fa-fw fa-user"></span> {tex('your_answer')}
              </NavItem>
              <NavItem eventKey="second" onSelect={() => this.handleSelect('second')}>
                <span className="fa fa-fw fa-check"></span> {tex('expected_answer')}
              </NavItem>
            </Nav>
            <div ref={(el) => { this.container = el }} id={`jsplumb-container-${this.props.item.id}`} className="jsplumb-container" style={{position:'relative'}}>
              <Tab.Content animation>
                <Tab.Pane eventKey="first">
                  <span className="help-block">
                    <span className="fa fa-info-circle"></span>{tex('match_player_click_link_help')}
                  </span>
                  <div id={`match-question-paper-${this.props.item.id}-first`} className="match-items row">
                    <div className="item-col col-md-5 col-sm-5 col-xs-5">
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
                    <div className="divide-col col-md-2 col-sm-2 col-xs-2" id={`popover-container-${this.props.item.id}`}>
                      { this.state.showPopover &&
                          <MatchLinkPopover
                            top={this.state.top}
                            solution={this.state.current}
                            showScore={this.props.showScore}
                          />
                        }
                    </div>
                    <div className="item-col col-md-5 col-sm-5 col-xs-5">
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
                </Tab.Pane>

                <Tab.Pane eventKey="second">
                  <span className="help-block" style={{visibility:'hidden'}} >
                    <span className="fa fa-info-circle"></span>{tex('match_player_click_link_help')}
                  </span>
                  <div id={`match-question-paper-${this.props.item.id}-second`} className="match-items row">
                    <div className="item-col col-md-5 col-sm-5 col-xs-5">
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

                    <div className="divide-col col-md-2 col-sm-2 col-xs-2" />

                    <div className="item-col col-md-5 col-sm-5 col-xs-5">
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

                    <div className="match-associations col-md-12">
                      {this.props.item.solutions.map((solution) =>
                        <div
                          key={`solution-${solution.firstId}-${solution.secondId}`}
                          className={classes(
                            'answer-item',
                            {'selected-answer' : solution.score > 0}
                          )}
                        >
                          <div className="sets">
                            <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionData(solution.firstId, this.props.item.firstSet)}} />
                            <span className="fa fa-fw fa-chevron-left"></span>
                            <span className="fa fa-fw fa-chevron-right"></span>
                            <div className="item-content" dangerouslySetInnerHTML={{__html: utils.getSolutionData(solution.secondId, this.props.item.secondSet)}} />
                          </div>
                          <Feedback
                            id={`answer-${solution.firstId}-${solution.secondId}-feedback`}
                            feedback={solution.feedback}
                          />
                          {this.props.showScore &&
                            <SolutionScore score={solution.score}/>
                          }                          
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
  answer: T.array,
  showScore: T.bool.isRequired
}

MatchPaper.defaultProps = {
  answer: []
}
