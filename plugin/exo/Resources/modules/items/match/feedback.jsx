import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'
import {tex} from '#/main/core/translation'
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
    <span className={classes(
      'fa fa-fw',
      {'fa-check text-success' : props.solution.score > 0},
      {'fa-times text-danger' : props.solution.score <= 0 }
    )}>
    </span>
    {props.solution.feedback &&
      <div className="match-association-feedback" dangerouslySetInnerHTML={{__html: props.solution.feedback}}/>
    }
  </Popover>


MatchLinkPopover.propTypes = {
  top: T.number.isRequired,
  solution: T.object.isRequired
}

const MatchItem = props =>
  <div
    id={`${props.type}_${props.item.id}`}
    className={classes('answer-item match-item', props.type)}
    dangerouslySetInnerHTML={{__html: props.item.data}}
  >
  </div>

MatchItem.propTypes = {
  type: T.string.isRequired,
  item: T.object.isRequired
}

export class MatchFeedback extends Component
{
  constructor(props) {
    super(props)
    this.state = {
      showPopover: false
    }

    this.jsPlumbInstance = utils.getJsPlumbInstance(false)
    this.container = null
    this.handleWindowResize = this.handleWindowResize.bind(this)
    this.handleConnectionClick = this.handleConnectionClick.bind(this)
  }

  drawAnswers(){
    for (const answer of this.props.answer) {
      const solution = this.props.item.solutions.find(solution => answer.firstId === solution.firstId && answer.secondId === solution.secondId)
      const connection = this.jsPlumbInstance.connect({
        source: 'source_' + answer.firstId,
        target: 'target_' + answer.secondId,
        type: solution && solution.score > 0 ? 'correct' : 'incorrect',
        deleteEndpointsOnDetach: true
      })

      const connectionClass = 'connection-' + answer.firstId + '-' + answer.secondId
      if (connection) { // connection doesn't exist in tests has jsPlumb is mocked
        connection.addClass(connectionClass)

        connection.bind('click', (conn) => {
          this.handleConnectionClick(conn)
        })
      }
    }
  }

  handleConnectionClick(connection) {

    const firstId = connection.sourceId.replace('source_', '')
    const secondId = connection.targetId.replace('target_', '')
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

  componentDidMount() {
    this.jsPlumbInstance.setContainer(this.container)
    window.addEventListener('resize', this.handleWindowResize)
    // we have to wait for elements to be at there right place before drawing... so... timeout
    window.setTimeout(() => {
      this.drawAnswers()
    }, 200)
  }

  componentWillUnmount(){
    window.removeEventListener('resize', this.handleWindowResize)
    utils.resetJsPlumb()

    this.jsPlumbInstance = null
    delete this.jsPlumbInstance
  }

  render() {
    return (
      <div className="match-feedback">
        <span className="help-block">
          <span className="fa fa-info-circle"></span> {tex('match_player_click_link_help')}
        </span>

        <div className="match-items row" ref={(el) => { this.container = el }} id={`match-question-paper-${this.props.item.id}-first`}>
          <div className="item-col col-md-5 col-sm-5 col-xs-5">
            <ul>
              {this.props.item.firstSet.map((item) =>
                <li key={'source_' + item.id}>
                  <MatchItem
                    item={item}
                    type="source"
                  />
                </li>
              )}
            </ul>
          </div>

          <div className="divide-col col-md-2 col-sm-2 col-xs-2" id={`popover-container-${this.props.item.id}`}>
            {this.state.showPopover &&
              <MatchLinkPopover
                top={this.state.top}
                solution={this.state.current}
              />
            }
          </div>

          <div className="item-col col-md-5 col-sm-5 col-xs-5">
            <ul>
              {this.props.item.secondSet.map((item) =>
                <li key={'target_' + item.id}>
                  <MatchItem
                    item={item}
                    type="target"
                  />
                </li>
              )}
            </ul>
          </div>
        </div>
      </div>
    )
  }
}

MatchFeedback.propTypes = {
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

MatchFeedback.defaultProps = {
  answer: []
}
