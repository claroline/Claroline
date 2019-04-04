import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'

import {makeId} from '#/main/core/scaffolding/id'
import {Textarea} from '#/main/core/layout/form/components/field/textarea'

import {makeSortable, SORT_HORIZONTAL, SORT_VERTICAL} from '#/plugin/exo/utils/sortable'
import {SCORE_FIXED} from '#/plugin/exo/quiz/enums'
import {constants} from '#/plugin/exo/items/ordering/constants'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {OrderingItem as OrderingItemType} from '#/plugin/exo/items/ordering/prop-types'
import {OrderingItemDragPreview} from '#/plugin/exo/items/ordering/components/ordering-item-drag-preview'

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
      <div className="item">
        <div className="text-fields">
          <Textarea
            id={`item-${this.props.id}-data`}
            value={this.props.data}
            onChange={(data) => updateItem('data', data, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
          />
          {this.props.item.direction === constants.DIRECTION_VERTICAL && this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`item-${this.props.id}-feedback`}
                value={this.props.feedback}
                onChange={(text) => updateItem('feedback', text, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
              />
            </div>
          }
        </div>
        <div className="right-controls">
          {!this.props.fixedScore &&
            <input
              title={trans('score', {}, 'quiz')}
              type="number"
              min={this.props.isOdd ? '' : 0}
              max={this.props.isOdd ? 0 : ''}
              className="form-control item-score"
              value={this.props.score}
              onChange={(e) => updateItem('score', e.target.value, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            />
          }
          <Button
            id={`item-${this.props.id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={trans('choice_feedback_info', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`item-${this.props.id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            label={trans('delete')}
            callback={() => removeItem(this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            disabled={!this.props.deletable}
            tooltip="top"
          />
        </div>
        {this.props.item.direction === constants.DIRECTION_HORIZONTAL && this.state.showFeedback &&
          <div className="feedback-container">
            <Textarea
              id={`item-${this.props.id}-feedback`}
              value={this.props.feedback}
              onChange={(text) => updateItem('feedback', text, this.props.id, this.props.item.items, this.props.item.solutions, this.props.onChange)}
            />
          </div>
        }
      </div>
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
  isOdd: T.bool.isRequired
}

let OrderingItem = (props) =>
  props.connectDropTarget (
    <div className="item-container">
      <Item {...props} />
      {props.connectDragSource(
        <span
          title={trans('move')}
          draggable="true"
          className={classes(
            'tooltiped-button',
            'btn',
            'drag-handle'
          )}
        >
          <span className="fa fa-arrows"/>
        </span>
      )}
    </div>
  )

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
  index: T.number.isRequired
}

OrderingItem = makeSortable(OrderingItem, 'ORDERING_ITEM', OrderingItemDragPreview)

const OrderingOdd = props => {
  return (
    <div className="item-container negative-score">
      <Item {...props} />
    </div>
  )
}

OrderingOdd.propTypes = {
  item: T.shape(OrderingItemType.propTypes).isRequired,
  id: T.string.isRequired,
  data: T.string.isRequired,
  score: T.number.isRequired,
  feedback: T.string,
  fixedScore: T.bool.isRequired,
  onChange: T.func.isRequired
}

const ItemList = (props) =>
  <ul>
    {props.item.items.filter(i => props.isOdd ? undefined === i._position : undefined !== i._position).map((el, index) =>
      <li
        className={constants.DIRECTION_VERTICAL === props.item.direction ? constants.DIRECTION_VERTICAL : constants.DIRECTION_HORIZONTAL}
        key={el.id}>
        {props.isOdd ?
          <OrderingOdd
            id={el.id}
            data={el.data}
            score={el._score}
            feedback={el._feedback}
            deletable={true}
            fixedScore={SCORE_FIXED === props.item.score.type}
            {...props}
          /> :
          <OrderingItem
            sortDirection={constants.DIRECTION_VERTICAL === props.item.direction ? SORT_VERTICAL : SORT_HORIZONTAL}
            onSort={(a, b) => moveItem(a, b, props.item.items, props.item.solutions, props.onChange)}
            id={el.id}
            data={el.data}
            score={el._score}
            feedback={el._feedback}
            position={index}
            index={index}
            fixedScore={props.item.score.type === SCORE_FIXED}
            deletable={el._deletable}
            {...props}
          />
        }
      </li>
    )}
  </ul>

ItemList.propTypes = {
  item: T.shape(OrderingItemType.propTypes).isRequired,
  isOdd: T.bool.isRequired,
  onChange: T.func.isRequired
}

const OrderingItems = (props) =>
  <div>
    <div className="items-row">
      <ItemList
        {...props}
        isOdd={false}
      />
      <div className="item-footer">
        <button
          type="button"
          className="btn btn-default"
          onClick={() => addItem(props.item.items, props.item.solutions, false, props.onChange)}
        >
          <span className="fa fa-plus"/>
          {trans('ordering_add_item', {}, 'quiz')}
        </button>
      </div>
    </div>

    {props.item.mode === constants.MODE_BESIDE &&
      <div className="odd-row">
        <ItemList
          {...props}
          isOdd={true}
        />
        <div className="item-footer">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => addItem(props.item.items, props.item.solutions, true, props.onChange)}
          >
            <span className="fa fa-plus"/>
            {trans('ordering_add_odd', {}, 'quiz')}
          </button>
        </div>
      </div>
    }
  </div>

OrderingItems.propTypes = {
  item: T.shape(OrderingItemType.propTypes).isRequired,
  onChange: T.func.isRequired
}

const OrderingEditor = props =>
  <FormData
    className="ordering-item ordering-editor"
    embedded={true}
    name={props.formName}
    dataPart={props.path}
    sections={[
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
                props.update('items', props.item.items.filter(i => undefined !== i._position))
                props.update('solutions', props.item.solutions.filter(s => undefined !== s.position))
              }
            }
          }, {
            name: 'orderings',
            label: trans('orderings', {}, 'quiz'),
            hideLabel: true,
            required: true,
            render: (orderingItem) => {
              const Items = (
                <OrderingItems
                  item={orderingItem}
                  onChange={props.update}
                />
              )

              return Items
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(OrderingEditor, ItemEditorType, {
  item: T.shape(OrderingItemType.propTypes).isRequired
})

export {OrderingEditor}