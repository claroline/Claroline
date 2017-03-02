import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import shuffle from 'lodash/shuffle'

/* global jsPlumb */

function initJsPlumb(jsPlumbInstance) {
  // defaults parameters for all connections
  jsPlumbInstance.importDefaults({
    Anchors: ['RightMiddle', 'LeftMiddle'],
    ConnectionsDetachable: true,
    Connector: 'Straight',
    DropOptions: {tolerance: 'touch'},
    HoverPaintStyle: {strokeStyle: '#FC0000'},
    LogEnabled: true,
    PaintStyle: {strokeStyle: '#777', lineWidth: 4}
  })

  jsPlumbInstance.registerConnectionTypes({
    'valid': {
      paintStyle     : { strokeStyle: '#5CB85C', lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: 'green',   lineWidth: 6 }
    },
    'invalid': {
      paintStyle:      { strokeStyle: '#D9534F', lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: 'red',     lineWidth: 6 }
    },
    'selected': {
      paintStyle:      { strokeStyle: '#006DCC', lineWidth: 6 },
      hoverPaintStyle: { strokeStyle: '#006DCC', lineWidth: 6 }
    },
    'default': {
      paintStyle     : { strokeStyle: 'grey',    lineWidth: 5 },
      hoverPaintStyle: { strokeStyle: 'grey', lineWidth: 6 }
    }
  })
}

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
      <div className={classes('item', this.props.type)} id={this.props.type + '_' + this.props.item.id}>
        <div className="item-content" dangerouslySetInnerHTML={{__html: this.props.item.data}} />
      </div>
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

    this.jsPlumbInstance = jsPlumb.getInstance()
    initJsPlumb(this.jsPlumbInstance)

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

    // we have to wait for elements to be at there right place before drawing... so... timeout
    window.setTimeout(() => {
      drawAnswers(this.props.answer , this.jsPlumbInstance)
    }, 500)

    // use this event to create new answers
    this.jsPlumbInstance.bind('beforeDrop', (connection) => {
      // check that the connection is not already in jsPlumbConnections before creating it
      const list = this.jsPlumbInstance.getConnections().filter(el => el.sourceId === connection.sourceId && el.targetId === connection.targetId )

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
    jsPlumb.detachEveryConnection()
    // use reset instead of deleteEveryEndpoint because reset also remove event listeners
    jsPlumb.reset()
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
        <div id={`match-question-player-${this.props.item.id}`} className="match-question-player" ref={(el) => { this.container = el }}>
          <div className="item-col">
            <ul>
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
          <div className="divide-col" />
          <div className="item-col">
            <ul>
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
