import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Html} from '#/main/app/components/html'

import {utils} from '#/plugin/exo/items/match/utils'
import {Overlay} from '#/main/app/overlays/components/overlay'
import {SolutionPopover} from '#/plugin/exo/components/solution-popover'

const MatchItem = props =>
  <Html
    id={`${props.type}_${props.item.id}`}
    className={classes('match-item answer-item', props.type)}
  >
    {props.item.data}
  </Html>

MatchItem.propTypes = {
  type: T.string.isRequired,
  item: T.object.isRequired
}

class MatchExpectedAnswer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showPopover: false
    }

    this.drawAnswers = this.drawAnswers.bind(this)
    this.handleConnectionClick = this.handleConnectionClick.bind(this)
    this.handleWindowResize = this.handleWindowResize.bind(this)

    this.jsPlumbInstance = utils.getJsPlumbInstance(false)
    this.container = null
  }

  drawAnswers() {
    for (const solution of this.props.item.solutions) {
      if (solution.score > 0) {
        const connection = this.jsPlumbInstance.connect({
          source: 'source_' + solution.firstId,
          target: 'target_' + solution.secondId,
          type: 'selected',
          deleteEndpointsOnDetach: true
        })

        const connectionClass = 'connection-' + solution.firstId + '-' + solution.secondId
        if (connection) { // connection doesn't exist in tests has jsPlumb is mocked
          connection.addClass(connectionClass)

          connection.bind('click', (conn, e) => {
            this.handleConnectionClick(conn, e)
          })
        }
      }
    }
  }

  handleConnectionClick(connection, e) {
    const firstId = connection.sourceId.replace('source_', '')
    const secondId = connection.targetId.replace('target_', '')

    const solution = this.props.item.solutions.find(solution => solution.firstId === firstId && solution.secondId === secondId)
    if (this.state.showPopover) {
      this.setState({
        showPopover: false,
        current: {},
        target: null
      })
    } else {
      this.setState({
        showPopover: true,
        current: solution ? solution : {firstId: firstId, secondId: secondId, score: 0},
        target: e.target
      })
    }
  }

  handleWindowResize() {
    if (this.jsPlumbInstance) {
      this.jsPlumbInstance.repaintEverything()
    }
  }

  componentDidUpdate(prevProps) {
    // redraw connections
    if (get(prevProps, 'item.solutions') !== get(this.props, 'item.solutions')) {
      this.jsPlumbInstance.deleteEveryConnection()

      window.setTimeout(() => {
        this.drawAnswers()
      }, 200)
    }
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

    window.removeEventListener('resize', this.handleWindowResize)
  }

  render() {
    return (
      <div className="match-paper">
        <p className="text-body-secondary fs-sm">
          <span className="fa fa-circle-info me-2" aria-hidden={true} />
          {trans('match_answer_help', {}, 'quiz')}
        </p>

        <div ref={(el) => { this.container = el }} className="jtk-container position-relative">
          <div className="match-items row">
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

            <div
              className="divide-col col-md-2 col-sm-2 col-xs-2"
              ref={(el) => { this.popoverContainer = el }}
            >
              <Overlay
                show={this.state.showPopover && !!this.state.current}
                target={this.state.target}
                placement="bottom"
                container={this.popoverContainer}
                rootClose={true}
                rootCloseEvent="click"
                onHide={() => this.setState({showPopover: false})}
              >
                <SolutionPopover
                  solution={this.state.current}
                  showScore={this.props.showScore}
                  hasExpectedAnswers={this.props.item.hasExpectedAnswers}
                />
              </Overlay>
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
      </div>
    )
  }
}

MatchExpectedAnswer.propTypes = {
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
    description: T.string,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  showScore: T.bool.isRequired
}

export {
  MatchExpectedAnswer
}
