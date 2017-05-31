import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import shuffle from 'lodash/shuffle'

import {utils} from './utils/utils'

/* If any previous answer draw them */
function drawAnswers(answers, jsPlumbInstance){
  for (const answer of answers) {
    jsPlumbInstance.connect({
      source: 'source_' + answer.firstId,
      target: 'target_' + answer.secondId,
      type: 'default'
    })
  }
}

class MatchItem extends Component{
  constructor(props) {
    super(props)
  }

  componentDidMount(){
    this.props.onMount(this.props.type, this.props.type + '_' + this.props.item.id)
  }

  render() {
    return (
      <div
        className={classes('answer-item match-item', this.props.type)} id={this.props.type + '_' + this.props.item.id}
        dangerouslySetInnerHTML={{__html: this.props.item.data}}
      />
    )
  }
}

MatchItem.propTypes = {
  type: T.string.isRequired,
  item: T.object.isRequired,
  onMount: T.func.isRequired,
  onChange: T.func.isRequired
}

class MatchPlayer extends Component {

  constructor(props) {
    super(props)

    this.jsPlumbInstance = utils.getJsPlumbInstance(true)

    this.container = null
    this.handleWindowResize = this.handleWindowResize.bind(this)

    this.state = {
      firstSet: this.randomize(props.item.firstSet, props.item.random),
      secondSet: this.randomize(props.item.secondSet, props.item.random)
    }
  }

  randomize(items, random) {
    return random ? shuffle(items) : items
  }

  handleWindowResize() {
    this.jsPlumbInstance.repaintEverything()
  }

  componentDidMount() {
    this.jsPlumbInstance.setContainer(this.container)
    window.addEventListener('resize', this.handleWindowResize)
    const images =  document.images

    // required to fix position of anchors after images are loaded
    for (let i = 0; i < images.length; ++i) {
      images[i].addEventListener('load', this.handleWindowResize)
    }

    // we have to wait for elements to be at there right place before drawing... so... timeout
    window.setTimeout(() => {
      drawAnswers(this.props.answer, this.jsPlumbInstance)
    }, 500)

    // use this event to create new answers
    this.jsPlumbInstance.bind('beforeDrop', (connection) => {
      // check that the connection is not already in jsPlumbConnections before creating it
      const list = this.jsPlumbInstance.getConnections().filter(el => el.sourceId === connection.sourceId && el.targetId === connection.targetId)

      if (list.length > 0) {
        return false
      }

      //connection.connection.setType('selected')
      const firstId = connection.sourceId.replace('source_', '')
      const secondId = connection.targetId.replace('target_', '')

      // add answer
      this.props.onChange(
        [{firstId: firstId, secondId: secondId}].concat(this.props.answer)
      )

      return true
    })

    // remove jsPlumb connection
    this.jsPlumbInstance.bind('click', (connection) => {
      // this will fire beforeDetach interceptor
      this.jsPlumbInstance.detach(connection)
    })

    // remove answer
    this.jsPlumbInstance.bind('beforeDetach', (connection) => {
      const firstId = connection.sourceId.replace('source_', '')
      const secondId = connection.targetId.replace('target_', '')
      // remove answer
      this.props.onChange(
        this.props.answer.filter(answer => answer.firstId !== firstId || answer.secondId !== secondId)
      )
      return true
    })
  }

  componentWillUnmount(){
    window.removeEventListener('resize', this.handleWindowResize)

    utils.resetJsPlumb()

    this.jsPlumbInstance = null
    delete this.jsPlumbInstance
  }

  /**
   * When adding a firstSet or secondSet item we need to add an jsPlumb endpoint to it
   * In order to achieve that we need to wait for the new item to be mounted
  */
  itemDidMount(type, id){
    const isLeftItem = type === 'source'
    const selector = '#' +  id
    const anchor = isLeftItem ? 'RightMiddle' : 'LeftMiddle'

    window.setTimeout(() => {
      if (isLeftItem) {
        this.jsPlumbInstance.addEndpoint(this.jsPlumbInstance.getSelector(selector), {
          anchor: anchor,
          cssClass: 'endPoints',
          isSource: true,
          maxConnections: -1
        })
      } else {
        this.jsPlumbInstance.addEndpoint(this.jsPlumbInstance.getSelector(selector), {
          anchor: anchor,
          cssClass: 'endPoints',
          isTarget: true,
          maxConnections: -1
        })
      }
    }, 100)
  }

  render() {
    return (
        <div id={`match-question-player-${this.props.item.id}`} className="match-player match-items row" ref={(el) => { this.container = el }}>
          <div className="item-col col-md-5 col-sm-5 col-xs-5">
            <ul className="match-items-list">
            {this.state.firstSet.map((item) =>
              <li key={'source_' + item.id}>
                <MatchItem
                  onChange={this.props.onChange}
                  onMount={(type, id) => this.itemDidMount(type, id)}
                  item={item}
                  type="source"
                />
              </li>
            )}
            </ul>
          </div>

          <div className="divide-col col-md-2 col-sm-2 col-xs-2" />

          <div className="item-col col-md-5 col-sm-5 col-xs-5">
            <ul className="match-items-list">
            {this.state.secondSet.map((item) =>
              <li key={'target_' + item.id}>
                <MatchItem
                  onChange={this.props.onChange}
                  onMount={(type, id) => this.itemDidMount(type, id)}
                  item={item}
                  type="target"
                />
              </li>
            )}
            </ul>
          </div>
        </div>
    )
  }
}

MatchPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    random: T.bool.isRequired,
    firstSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    secondSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired
  }).isRequired,
  answer: T.array.isRequired,
  onChange: T.func.isRequired
}

MatchPlayer.defaultProps = {
  answer: []
}

export {MatchPlayer}
