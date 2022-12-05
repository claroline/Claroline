import React, {Component} from 'react'
import classes from 'classnames'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'

import Popover from 'react-bootstrap/lib/Popover'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {SCORE_SUM} from '#/plugin/exo/quiz/enums'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {emptyAnswer} from '#/plugin/exo/items/utils'
import {MatchItem as MatchItemTypes} from '#/plugin/exo/items/match/prop-types'
import {utils} from '#/plugin/exo/items/match/utils'

const getRightItemDeletable = (item) =>
  (item.secondSet.length > 1 && item.firstSet.length > 1) || (item.secondSet.length > 2 && item.firstSet.length === 1)

const getLeftItemDeletable = (item) =>
  (item.secondSet.length > 1 && item.firstSet.length > 1) || (item.secondSet.length === 1 && item.firstSet.length > 2)

function getPopoverPosition(connectionClass, id) {
  const containerRect =  document.getElementById('popover-place-holder-' + id).getBoundingClientRect()
  const connectionRect =  document.querySelectorAll('.' + connectionClass)[0].getBoundingClientRect()
  // only compute top position
  return {
    top:  connectionRect.top + connectionRect.height / 2 - containerRect.top
  }
}

function drawSolutions(solutions, jsPlumbInstance) {
  for (const solution of solutions) {
    const connection = jsPlumbInstance.connect({
      source: 'source_' + solution.firstId,
      target: 'target_' + solution.secondId,
      type: solution.score > 0 ? 'expected':'unexpected'
    })

    const connectionClass = 'connection-' + solution.firstId + '-' + solution.secondId
    if (connection) { // connection doesn't exist in tests has jsPlumb is mocked
      connection.addClass(connectionClass)
    }
  }
}

class MatchLinkPopover extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showFeedback : false
    }
  }

  render() {
    return (
      <Popover
        id={`popover-${this.props.solution.firstId}-${this.props.solution.secondId}`}
        positionTop={this.props.popover.top}
        className={classes('', this.props.hasExpectedAnswers && {
          'unexpected-answer' : 0 >= this.props.solution.score,
          'expected-answer' : 0 < this.props.solution.score
        })}
        placement="bottom"
        title={
          <div>
            {trans('match_edit_connection', {}, 'quiz')}

            <div className="popover-actions">
              <Button
                id={`match-connection-${this.props.solution.firstId}-${this.props.solution.secondId}-delete`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash"
                label={trans('delete', {}, 'actions')}
                disabled={!this.props.deletable}
                callback={() => this.props.handleConnectionDelete(this.props.solution.firstId, this.props.solution.secondId)}
                tooltip="top"
                dangerous={true}
              />

              <Button
                id={`match-connection-${this.props.solution.firstId}-${this.props.solution.secondId}-close`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-times"
                label={trans('close', {}, 'actions')}
                callback={() => this.props.handlePopoverClose()}
                tooltip="top"
              />
            </div>
          </div>
        }
      >
        <div className="association">
          {this.props.hasExpectedAnswers && this.props.hasScore &&
            <input
              className="form-control association-score"
              type="number"
              value={this.props.solution.score}
              onChange={(e) => {
                const newSolution = cloneDeep(this.props.solution)
                newSolution.score = parseFloat(e.target.value)
                this.props.update(this.props.path, newSolution)
              }}
            />
          }
          {this.props.hasExpectedAnswers && !this.props.hasScore &&
            <input
              type="checkbox"
              checked={0 < this.props.solution.score}
              onChange={(e) => {
                const newSolution = cloneDeep(this.props.solution)
                newSolution.score = e.target.checked ? 1 : 0
                this.props.update(this.props.path, newSolution)
              }}
            />
          }

          <Button
            id={`solution-${this.props.solution.firstId}-${this.props.solution.secondId}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments"
            label={trans('feedback_association_created', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />
        </div>

        {this.state.showFeedback &&
          <HtmlInput
            id={`solution-${this.props.solution.firstId}-${this.props.solution.secondId}-feedback`}
            className="feedback-control"
            value={this.props.solution.feedback}
            onChange={
              feedback => {
                const newSolution = cloneDeep(this.props.solution)
                newSolution.feedback = feedback
                this.props.update(this.props.path, newSolution)
              }
            }
          />
        }
      </Popover>
    )
  }
}

MatchLinkPopover.propTypes = {
  path: T.string.isRequired,
  popover: T.object.isRequired,
  solution: T.object.isRequired,
  deletable: T.bool.isRequired,
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  handlePopoverClose: T.func.isRequired,
  handleConnectionDelete: T.func.isRequired,
  update: T.func.isRequired
}

class MatchItem extends Component{
  componentDidMount(){
    this.props.onMount(this.props.type, this.props.type + '_' + this.props.item.id)
  }

  render() {
    return (
      <div className={classes('answer-item match-item', this.props.type)} id={this.props.type + '_' + this.props.item.id}>
        {this.props.type === 'source' &&
          <div className="left-controls">
            <Button
              id={`match-source-${this.props.item.id}-delete`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash"
              label={trans('delete', {}, 'actions')}
              disabled={!this.props.deletable}
              callback={() => this.props.onUnmount(this.props.item.id, this.props.type + '_' + this.props.item.id)}
              tooltip="top"
              dangerous={true}
            />
          </div>
        }

        <div className="text-fields">
          <HtmlInput
            id={`${this.props.type}-${this.props.item.id}-data`}
            value={this.props.item.data}
            onChange={data => this.props.update('data', data)}
            onChangeMode={this.props.repaint}
            minRows={1}
          />
        </div>

        {this.props.type === 'target' &&
          <div className="right-controls">
            <Button
              id={`match-target-${this.props.type + '_' + this.props.item.id}-delete`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash"
              label={trans('delete', {}, 'actions')}
              disabled={!this.props.deletable}
              callback={() => this.props.onUnmount(this.props.item.id, this.props.type + '_' + this.props.item.id)}
              tooltip="top"
              dangerous={true}
            />
          </div>
        }
      </div>
    )
  }
}

MatchItem.propTypes = {
  type: T.string.isRequired,
  item: T.object.isRequired,
  deletable: T.bool.isRequired,

  onMount: T.func.isRequired,
  onUnmount: T.func.isRequired,
  update: T.func.isRequired,
  repaint: T.func.isRequired
}

class MatchElements extends Component {
  constructor(props) {
    super(props)

    this.state = {
      popover: {
        visible: false,
        top: 0
      },

      showFeedback : false,
      jsPlumbConnection: null,
      current: null
    }

    this.container = null

    this.jsPlumbInstance = utils.getJsPlumbInstance()

    this.handleTextEditorSwitch = this.handleTextEditorSwitch.bind(this)
    this.handleWindowResize = this.handleWindowResize.bind(this)
    this.removeConnection = this.removeConnection.bind(this)
  }

  handleTextEditorSwitch() {
    // timeout is because we don't now when tinymce as finished loading
    window.setTimeout(() => {
      this.jsPlumbInstance.repaintEverything()
    }, 300)
  }

  handleWindowResize() {
    this.jsPlumbInstance.repaintEverything()
  }

  componentDidMount() {
    this.jsPlumbInstance.setContainer(this.container)
    // events that need to call jsPlumb repaint method...
    window.addEventListener('resize', this.handleWindowResize)

    // we have to wait for elements to be at there right place before drawing... so... timeout
    window.setTimeout(() => {
      drawSolutions(this.props.item.solutions , this.jsPlumbInstance)
    }, 500)

    // use this event to create solutions instead of 'connection' event
    this.jsPlumbInstance.bind('beforeDrop', (connection) => {
      // check that the connection is not already in jsPlumbConnections before creating it
      const list = this.jsPlumbInstance.getConnections().filter(el => el.sourceId === connection.sourceId && el.targetId === connection.targetId )

      if (list.length > 0) {
        return false
      }

      connection.connection.setType('selected')
      const firstId = connection.sourceId.replace('source_', '')
      const secondId = connection.targetId.replace('target_', '')
      const connectionClass = 'connection-' + firstId + '-' + secondId
      if (connection) {
        connection.connection.addClass(connectionClass)
      }
      const positions = getPopoverPosition(connectionClass, this.props.item.id)

      const solution = {
        firstId: firstId,
        secondId: secondId,
        feedback: '',
        score: 1
      }
      // add solution to store
      const newItem = cloneDeep(this.props.item)
      newItem.solutions.push(solution)
      this.props.update('solutions', newItem.solutions)

      const solutionIndex = newItem.solutions.findIndex(solution => solution.firstId === firstId && solution.secondId === secondId)

      this.setState({
        popover: {
          visible: true,
          top: positions.top
        },
        jsPlumbConnection: connection,
        current: solutionIndex
      })

      return true

    })

    // configure connection
    this.jsPlumbInstance.bind('click', (connection) => {
      connection.setType('selected')

      const firstId = connection.sourceId.replace('source_', '')
      const secondId = connection.targetId.replace('target_', '')
      const connectionClass = 'connection-' + firstId + '-' + secondId
      const positions = getPopoverPosition(connectionClass, this.props.item.id)
      const solutionIndex = this.props.item.solutions.findIndex(el => el.firstId === firstId && el.secondId === secondId)

      this.setState({
        popover: {
          visible: true,
          top: positions.top
        },
        jsPlumbConnection: connection,
        current: solutionIndex
      })
    })

    // configure connection
    this.jsPlumbInstance.bind('click', (connection) => {
      connection.setType('selected')

      const firstId = connection.sourceId.replace('source_', '')
      const secondId = connection.targetId.replace('target_', '')
      const connectionClass = 'connection-' + firstId + '-' + secondId
      const positions = getPopoverPosition(connectionClass, this.props.item.id)
      const solutionIndex = this.props.item.solutions.findIndex(el => el.firstId === firstId && el.secondId === secondId)

      this.setState({
        popover: {
          visible: true,
          top: positions.top
        },
        jsPlumbConnection: connection,
        current: solutionIndex
      })
    })
  }

  itemWillUnmount(isLeftSet, id, elemId) {
    // remove item endpoint
    // https://jsplumbtoolkit.com/community/doc/miscellaneous-examples.html
    // Remove all Endpoints for the element, deleting their Connections.
    // not sure about this one especially concerning events
    this.jsPlumbInstance.removeAllEndpoints(elemId)

    // remove item from list
    const newItem = cloneDeep(this.props.item)
    if (isLeftSet) {
      const setIndex = newItem.firstSet.findIndex(set => set.id === id)
      newItem.firstSet.splice(setIndex, 1)
    } else {
      const setIndex = newItem.secondSet.findIndex(set => set.id === id)
      newItem.secondSet.splice(setIndex, 1)
    }

    // remove related solution
    newItem.solutions = newItem.solutions.filter(solution => isLeftSet ? solution.firstId !== id : solution.secondId !== id)

    // forward update
    this.props.update(null, newItem)
  }

  /**
   * When adding a firstSet or secondSet item we need to add an jsPlumb endpoint to it
   * In order to achieve that we need to wait for the new item to be mounted
  */
  itemDidMount(type, id) {
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

  removeConnection(firstId, secondId) {
    // remove jsPlumb connection
    this.jsPlumbInstance.deleteConnection(this.state.jsPlumbConnection.connection)
    this.setState({
      popover: {
        visible: false
      },
      jsPlumbConnection: null,
      current: null
    })

    // remove solution from item
    const newSolutions = cloneDeep(this.props.item.solutions)
    const solutionIndex = newSolutions.findIndex(solution => solution.firstId === firstId &&solution.secondId == secondId)
    if (-1 !== solutionIndex) {
      newSolutions.splice(solutionIndex, 1)
    }

    this.props.update('solutions', newSolutions)
  }

  closePopover() {
    this.setState({popover: {visible: false}})
    const list = this.jsPlumbInstance.getConnections()

    for(const conn of list){
      let type = 'expected'
      const firstId = conn.sourceId.replace('source_', '')
      const secondId = conn.targetId.replace('target_', '')
      const solution = this.props.item.solutions.find(solution => solution.firstId === firstId && solution.secondId === secondId)
      if (undefined !== solution && solution.score <= 0){
        type = 'unexpected'
      }
      conn.setType(type)
    }
  }

  /**
   * We need to tell jsPlumb to repaint each time something make the form changing it's size
   * For now this handle :
   * - Error message show / hide
   * - Item deletion -> if any other item is below the one that is currently deleted it's follower will go up but the endpoint stay at the previous place
   */
  componentDidUpdate(prevProps) {
    if ((prevProps.item.firstSet.length > this.props.item.firstSet.length || prevProps.item.secondSet.length > this.props.item.secondSet.length) || get(this.props.item, '_touched')) {
      this.jsPlumbInstance.repaintEverything()
    }
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.handleWindowResize)

    utils.resetJsPlumb()

    this.jsPlumbInstance = null
    delete this.jsPlumbInstance
  }

  render() {
    return (
      <div
        id={`match-question-editor-id-${this.props.item.id}`}
        className="match-items row"
        ref={(el) => { this.container = el }}
      >
        <div className="item-col col-md-5 col-sm-5 col-xs-5">
          <ul>
            {this.props.item.firstSet.map((item, key) =>
              <li key={'source_' + item.id}>
                <MatchItem
                  type="source"
                  item={item}
                  deletable={getLeftItemDeletable(this.props.item)}
                  update={(prop, value) => this.props.update(`firstSet[${key}].${prop}`, value)}
                  onMount={(type, id) => this.itemDidMount(type, id)}
                  onUnmount={(id, elemId) => this.itemWillUnmount(true, id, elemId)}
                  repaint={this.handleTextEditorSwitch}
                />
              </li>
            )}
          </ul>

          <Button
            type={CALLBACK_BUTTON}
            className="btn btn-block"
            icon="fa fa-fw fa-plus"
            label={trans('match_add_item', {}, 'quiz')}
            callback={() => {
              const newItems = cloneDeep(this.props.item.firstSet)
              newItems.push(emptyAnswer())

              this.props.update('firstSet', newItems)
            }}
          />
        </div>

        <div id={`popover-place-holder-${this.props.item.id}`} className="divide-col col-md-2 col-sm-2 col-xs-2">
          {this.state.popover.visible && null !== this.state.current && this.props.item.solutions[this.state.current] &&
            <MatchLinkPopover
              handleConnectionDelete={(firstId, secondId) => this.removeConnection(firstId, secondId)}
              handlePopoverClose={() => this.closePopover()}
              popover={this.state.popover}
              solution={this.props.item.solutions[this.state.current]}
              deletable={this.props.item.solutions.length > 1}
              hasScore={this.props.hasAnswerScores}
              hasExpectedAnswers={this.props.item.hasExpectedAnswers}
              path={`solutions[${this.state.current}]`}
              update={this.props.update}
            />
          }
        </div>

        <div className="item-col col-md-5 col-sm-5 col-xs-5">
          <ul>
            {this.props.item.secondSet.map((item, key) =>
              <li key={'target_' + item.id}>
                <MatchItem
                  type="target"
                  item={item}
                  deletable={getRightItemDeletable(this.props.item)}
                  update={(prop, value) => this.props.update(`secondSet[${key}].${prop}`, value)}
                  onMount={(type, id) => this.itemDidMount(type, id)}
                  onUnmount={(id, elemId) => this.itemWillUnmount(false, id, elemId)}
                  repaint={this.handleTextEditorSwitch}
                />
              </li>
            )}
          </ul>

          <Button
            type={CALLBACK_BUTTON}
            className="btn btn-block"
            icon="fa fa-fw fa-plus"
            label={trans('match_add_item', {}, 'quiz')}
            callback={() => {
              const newItems = cloneDeep(this.props.item.secondSet)
              newItems.push(emptyAnswer())

              this.props.update('secondSet', newItems)
            }}
          />
        </div>
      </div>
    )
  }
}

const MatchEditor = props => {
  const MatchComponent = (
    <MatchElements
      {...props}
      item={props.item}
      hasAnswerScores={props.hasAnswerScores}
    />
  )

  return (
    <FormData
      className="match-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'solutions',
              label: trans('answers', {}, 'quiz'),
              required: true,
              component: MatchComponent
            }, {
              name: 'random',
              label: trans('shuffle_answers', {}, 'quiz'),
              help: [
                trans('shuffle_answers_help', {}, 'quiz'),
                trans('shuffle_answers_results_help', {}, 'quiz')
              ],
              type: 'boolean'
            }, {
              name: 'penalty',
              type: 'number',
              label: trans('editor_penalty_label', {}, 'quiz'),
              required: true,
              displayed: (item) => item.hasExpectedAnswers && props.hasAnswerScores && item.score.type === SCORE_SUM
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(MatchEditor, ItemEditorTypes, {
  item: T.shape(
    MatchItemTypes.propTypes
  ).isRequired
})

export {
  MatchEditor
}
