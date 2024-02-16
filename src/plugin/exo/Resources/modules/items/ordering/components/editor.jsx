import React, {Component, forwardRef} from 'react'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'
import {makeId} from '#/main/core/scaffolding/id'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {makeSortable, SORT_HORIZONTAL, SORT_VERTICAL} from '#/plugin/exo/utils/sortable'
import {SCORE_FIXED} from '#/plugin/exo/quiz/enums'
import {constants} from '#/plugin/exo/items/ordering/constants'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {OrderingItem as OrderingItemType} from '#/plugin/exo/items/ordering/prop-types'
import {OrderingItemDragPreview} from '#/plugin/exo/items/ordering/components/ordering-item-drag-preview'
import {FeedbackEditorButton} from '#/plugin/exo/buttons/feedback/components/button'

const addItem = (items, solutions, isOdd, saveCallback) => {
  const newItems = cloneDeep(items)
  const newSolutions = cloneDeep(solutions)
  const newSolution = {
    itemId: makeId(),
    score: isOdd ? 0 : 1,
    feedback: '',
    position: isOdd ? undefined : newSolutions.filter(s => undefined !== s.position).length + 1
  }
  newSolutions.push(newSolution)
  newItems.push({
    id: newSolution.itemId,
    type: 'text/html',
    data: '',
    _deletable: true,
    _score: newSolution.score,
    _feedback: '',
    _position: newSolution.position
  })
  newItems.forEach(i => i._deletable = 2 < newSolutions.filter(s => undefined !== s.position).length)

  saveCallback('solutions', newSolutions)
  saveCallback('items', newItems)
}

const updateItem = (property, value, itemId, items, solutions, saveCallback) => {
  const newItems = cloneDeep(items)
  const formattedValue = 'score' === property ? parseFloat(value) : value
  const itemIndex = newItems.findIndex(i => i.id === itemId)
  const decoratedName = 'data' === property ? 'data' : `_${property}`
  newItems[itemIndex][decoratedName] = formattedValue

  saveCallback('items', newItems)

  if (-1 < ['score', 'feedback'].indexOf(property)) {
    const newSolutions = cloneDeep(solutions)
    const solutionIndex = newSolutions.findIndex(s => s.itemId === itemId)
    newSolutions[solutionIndex][property] = formattedValue

    saveCallback('solutions', newSolutions)
  }
}

const moveItem = (itemId, swapItemId, items, solutions, saveCallback) => {
  const newItems = cloneDeep(items)
  const newSolutions = cloneDeep(solutions)

  // previous index of the dragged item
  const itemIndex = newItems.findIndex(i => i.id === itemId)
  const solution = newSolutions.find(s => s.itemId === itemId)
  // new index of the dragged item
  const swapItemIndex = newItems.findIndex(i => i.id === swapItemId)
  const swapSolution = newSolutions.find(s => s.itemId === swapItemId)

  const tempItem = newItems.find(i => i.id === itemId)
  const tempSwapItem = newItems.find(i => i.id === swapItemId)
  tempItem._position = swapItemIndex + 1
  tempSwapItem._position = itemIndex + 1

  newItems[swapItemIndex] = tempItem
  newItems[itemIndex] = tempSwapItem

  // update solutions
  solution.position = swapItemIndex + 1
  swapSolution.position = itemIndex + 1

  saveCallback('items', newItems)
  saveCallback('solutions', newSolutions)
}

const removeItem = (itemId, items, solutions, saveCallback) => {
  const newItems = cloneDeep(items)
  const newSolutions = cloneDeep(solutions)
  const itemIndex = newItems.findIndex(i => i.id === itemId)
  const solutionIndex = newSolutions.findIndex(s => s.itemId === itemId)

  if (-1 < itemIndex) {
    newItems.splice(itemIndex, 1)
  }
  if (-1 < solutionIndex) {
    newSolutions.splice(solutionIndex, 1)
  }
  newItems.forEach(i => i._deletable = 2 < newSolutions.filter(s => undefined !== s.position).length)

  saveCallback('items', newItems)
  saveCallback('solutions', newSolutions)
}

class Item extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <>
        <div className="text-fields">
          <HtmlInput
            id={`item-${this.props.id}-data`}
            value={this.props.data}
            onChange={(data) => updateItem('data', data, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            minRows={1}
          />

          {this.state.showFeedback &&
            <HtmlInput
              id={`item-${this.props.id}-feedback`}
              className="feedback-control"
              value={this.props.feedback}
              onChange={(text) => updateItem('feedback', text, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            />
          }
        </div>

        <div className="right-controls">
          {this.props.item.hasExpectedAnswers && this.props.hasScore && !this.props.fixedScore &&
            <input
              title={trans('score', {}, 'quiz')}
              type="number"
              min={this.props.isOdd ? '' : 0}
              max={this.props.isOdd ? 0 : ''}
              className="form-control score"
              value={this.props.score}
              onChange={(e) => updateItem('score', e.target.value, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            />
          }

          <FeedbackEditorButton
            id={this.props.id}
            label={trans('choice_feedback_info', {}, 'quiz')}
            feedback={this.props.feedback}
            toggle={() => this.setState({showFeedback: !this.state.showFeedback})}
          />

          <Button
            id={`item-${this.props.id}-delete`}
            className="btn btn-text-secondary"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash"
            label={trans('delete', {}, 'actions')}
            callback={() => removeItem(this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            disabled={!this.props.deletable}
            tooltip="top"
            dangerous={true}
          />

          {!this.props.isOdd &&
            <TooltipOverlay
              id={`ordering-item-${this.props.id}-drag`}
              tip={trans('move', {}, 'actions')}
              disabled={this.props.isDragging}
            >
              {this.props.connectDragSource(
                <span
                  title={trans('move', {}, 'actions')}
                  draggable="true"
                  className="btn btn-text-secondary drag-handle"
                >
                  <span className="fa fa-fw fa-arrows" />
                </span>
              )}
            </TooltipOverlay>
          }
        </div>
      </>
    )
  }
}

Item.propTypes = {
  item: T.shape(OrderingItemType.propTypes).isRequired,
  id: T.string.isRequired,
  data: T.string.isRequired,
  score: T.number.isRequired,
  feedback: T.string,
  position: T.number,
  fixedScore: T.bool.isRequired,
  deletable: T.bool.isRequired,
  onChange: T.func.isRequired,
  isOdd: T.bool.isRequired,
  isDragging: T.bool,
  connectDragSource: T.func,
  hasScore: T.bool.isRequired
}

let OrderingItem = forwardRef((props, ref) =>
  props.connectDropTarget(
    <li className="ordering-answer-item answer-item" ref={ref}>
      <Item {...props} isOdd={false} />
    </li>
  )
)

OrderingItem.displayName = 'OrderingItem'

OrderingItem.propTypes = {
  item: T.shape(OrderingItemType.propTypes).isRequired,
  id: T.string.isRequired,
  data: T.string.isRequired,
  score: T.number.isRequired,
  feedback: T.string,
  position: T.number,
  fixedScore: T.bool.isRequired,
  deletable: T.bool.isRequired,
  onChange: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  connectDropTarget: T.func.isRequired,
  onSort: T.func.isRequired,
  index: T.number.isRequired,
  hasScore: T.bool.isRequired
}

OrderingItem = makeSortable(OrderingItem, 'ORDERING_ITEM', OrderingItemDragPreview)

const OrderingOdd = props =>
  <li className={classes('ordering-answer-item answer-item', {'unexpected-answer': props.item.hasExpectedAnswers})}>
    <Item {...props} isOdd={true} />
  </li>

OrderingOdd.propTypes = {
  item: T.shape(OrderingItemType.propTypes).isRequired,
  id: T.string.isRequired,
  data: T.string.isRequired,
  score: T.number.isRequired,
  feedback: T.string,
  fixedScore: T.bool.isRequired,
  onChange: T.func.isRequired,
  hasScore: T.bool.isRequired
}

const OrderingItems = (props) =>
  <>
    {0 === props.items.length &&
      <div className="empty-placeholder empty-placeholder-md">{trans('no_item_info', {}, 'quiz')}</div>
    }

    {0 !== props.items.length &&
      <ul className={classes('ordering-answer-items', props.item.direction)}>
        {props.items.map((el, index) =>
          <OrderingItem
            key={el.id}
            sortDirection={constants.DIRECTION_VERTICAL === props.item.direction ? SORT_VERTICAL : SORT_HORIZONTAL}
            onSort={(a, b) => moveItem(a, b, props.item.items, props.item.solutions, props.onChange)}
            id={el.id}
            data={el.data}
            score={el._score}
            feedback={el._feedback}
            position={index}
            index={index}
            fixedScore={SCORE_FIXED === props.item.score.type}
            deletable={el._deletable}
            hasScore={props.hasScore}
            {...props}
          />
        )}
      </ul>
    }

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-outline-primary w-100"
      icon="fa fa-fw fa-plus"
      label={trans('ordering_add_item', {}, 'quiz')}
      callback={() => addItem(props.item.items, props.item.solutions, false, props.onChange)}
    />
  </>

OrderingItems.propTypes = {
  item: T.shape(
    OrderingItemType.propTypes
  ).isRequired,
  items: T.array,
  hasScore: T.bool.isRequired,
  onChange: T.func.isRequired
}

const OrderingOdds = (props) =>
  <>
    {0 === props.items.length &&
      <div className="empty-placeholder empty-placeholder-md">{trans('no_odd_info', {}, 'quiz')}</div>
    }

    {0 !== props.items.length &&
      <ul className={classes('ordering-answer-items', constants.DIRECTION_VERTICAL)}>
        {props.items.map(el =>
          <OrderingOdd
            key={el.id}
            id={el.id}
            data={el.data}
            score={el._score}
            feedback={el._feedback}
            deletable={true}
            fixedScore={SCORE_FIXED === props.item.score.type}
            hasScore={props.hasScore}
            {...props}
          />
        )}
      </ul>
    }

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-outline-primary w-100"
      icon="fa fa-fw fa-plus"
      label={trans('ordering_add_odd', {}, 'quiz')}
      callback={() => addItem(props.item.items, props.item.solutions, true, props.onChange)}
    />
  </>

OrderingOdds.propTypes = {
  item: T.shape(
    OrderingItemType.propTypes
  ).isRequired,
  items: T.array,
  hasScore: T.bool.isRequired,
  onChange: T.func.isRequired
}

const OrderingEditor = props => {
  const orderingItems = props.item.items
    .map(item => {
      const solution = props.item.solutions.find(s => s.itemId === item.id)

      return ({
        ...item,
        _score: solution.score,
        _position: solution.position || undefined,
        _feedback: solution.feedback || '',
        _deletable: props.item.solutions.filter(solution => undefined !== solution.position).length > 2
      })
    })

  const Items = (
    <OrderingItems
      item={props.item}
      items={orderingItems.filter(i => undefined !== i._position)}
      onChange={props.update}
      hasScore={props.hasAnswerScores}
    />
  )

  const Odds = (
    <OrderingOdds
      item={props.item}
      items={orderingItems.filter(i => undefined === i._position)}
      onChange={props.update}
      hasScore={props.hasAnswerScores}
    />
  )

  return (
    <FormData
      className="ordering-item ordering-editor mb-0 user-select-none"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'direction',
              label: trans('direction', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.DIRECTION_CHOICES
              }
            }, {
              name: 'mode',
              label: trans('mode'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.MODE_CHOICES
              },
              onChange: (value) => {
                if (constants.MODE_INSIDE === value) {
                  props.update('items', orderingItems.filter(i => undefined !== i._position))
                  props.update('solutions', props.item.solutions.filter(s => undefined !== s.position))
                }
              }
            }, {
              name: 'items',
              label: trans('answer', {}, 'quiz'),
              required: true,
              component: Items
            }, {
              name: 'odds',
              label: trans('odds', {}, 'quiz'),
              displayed: props.item.mode === constants.MODE_BESIDE,
              component: Odds
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(OrderingEditor, ItemEditorType, {
  item: T.shape(OrderingItemType.propTypes).isRequired
})

export {
  OrderingEditor
}
