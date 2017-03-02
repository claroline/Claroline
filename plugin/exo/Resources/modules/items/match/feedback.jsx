import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'
import {tex} from '../../utils/translate'

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
      <div className={classes('item', this.props.type)} id={`${this.props.type}_${this.props.item.id}`}>
        <div className="item-content" dangerouslySetInnerHTML={{__html: this.props.item.data}} />
      </div>
    )
  }
}

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

    this.jsPlumbInstance = jsPlumb.getInstance()
    initJsPlumb(this.jsPlumbInstance)
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
        type: solution && solution.score > 0 ? 'green' : 'red',
        deleteEndpointsOnDetach:true
      })

      const connectionClass = 'connection-' + answer.firstId + '-' + answer.secondId
      connection.addClass(connectionClass)

      connection.bind('click', (conn) => {
        this.handleConnectionClick(conn)
      })
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
    jsPlumb.detachEveryConnection()
    // use reset instead of deleteEveryEndpoint because reset also remove event listeners
    jsPlumb.reset()
    this.jsPlumbInstance = null
    delete this.jsPlumbInstance
  }

  render() {
    return (
      <div>
        <span className="help-block">
          <span className="fa fa-info-circle">&nbsp;</span>{tex('match_player_click_link_help')}
        </span>      
        <div ref={(el) => { this.container = el }} id={`match-question-paper-${this.props.item.id}-first`} className="match-question-feedback">
        <div className="item-col">
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
