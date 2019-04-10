import React, {Component} from 'react'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {makeId} from '#/main/core/scaffolding/id'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {utils} from '#/plugin/exo/items/pair/utils'
import {PairItem as PairItemType} from '#/plugin/exo/items/pair/prop-types'
import {PairItemDragPreview} from '#/plugin/exo/items/pair/components/pair-item-drag-preview.jsx'

const addItem = (items, solutions, isOdd, saveCallback) => {
  const newItems = cloneDeep(items)
  const id = makeId()
  newItems.push({
    id: id,
    type: 'text/html',
    data: ''
  })

  if (isOdd) {
    const newSolutions = cloneDeep(solutions)
    newSolutions.push({
      itemIds: [id],
      score: 0,
      feedback: ''
    })
    saveCallback('solutions', newSolutions)
  }

  const itemDeletable = 2 < utils.getRealItemlist(newItems, solutions).length
  newItems.forEach(el => el._deletable = itemDeletable)

  saveCallback('items', newItems)
}

const updateItem = (property, value, itemId, items, solutions, isOdd, saveCallback) => {
  const newItems = cloneDeep(items)
  const formattedValue = 'score' === property ? parseFloat(value) : value
  const itemToUpdate = newItems.find(i => i.id === itemId)

  if (itemToUpdate) {
    if (isOdd) {
      if ('data' === property) {
        itemToUpdate[property] = formattedValue
      } else {
        const newSolutions = cloneDeep(solutions)
        const oddSolution = newSolutions.find(s => s.itemIds[0] === itemId)
        oddSolution[property] = formattedValue
        saveCallback('solutions', newSolutions)
      }
    } else {
      itemToUpdate[property] = formattedValue

      if ('data' === property) {
        const newSolutions = cloneDeep(solutions)
        newSolutions.map((solution) => {
          const solutionItemIdIndex = solution.itemIds.findIndex(id => id === itemId)

          if(-1 < solutionItemIdIndex){
            solution._data = value
          }
        })
        saveCallback('solutions', newSolutions)
      }
    }

    saveCallback('items', newItems)
  }
}

const removeItem = (itemId, items, solutions, isOdd, saveCallback) => {
  const newItems = cloneDeep(items)
  const index = newItems.findIndex(i => i.id === itemId)

  if (-1 < index) {
    newItems.splice(index, 1)
    const newSolutions = cloneDeep(solutions)

    if (isOdd) {
      // removes solution associated to odd item
      const solutionOddItemIdIndex = newSolutions.findIndex(s => s.itemIds.length === 1 && s.itemIds[0] === itemId)
      newSolutions.splice(solutionOddItemIdIndex, 1)
    } else {
      const itemDeletable = 2 < utils.getRealItemlist(newItems, solutions).length
      newItems.forEach(el => el._deletable = itemDeletable)

      // removes item from solution associations by replacing its value by default one (-1)
      newSolutions.forEach((solution) => {
        const solutionItemIdIndex = solution.itemIds.findIndex(id => id === itemId)

        if (-1 < solutionItemIdIndex) {
          const solutionItem = newSolutions.find(el => el.itemIds[solutionItemIdIndex] === itemId)
          // solutionItem.itemIds.splice(solutionItemIdIndex, 1)
          solutionItem.itemIds[solutionItemIdIndex] = -1
        }
      })
    }

    saveCallback('items', newItems)
    saveCallback('solutions', newSolutions)
  }
}

const addPair = (solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  newSolutions.push({
    itemIds: [-1, -1],
    score: 1,
    feedback: '',
    ordered: false
  })

  saveCallback('rows', newSolutions.filter(s => 0 < s.score).length)

  const realSolutions = utils.getRealSolutionList(newSolutions)
  realSolutions.forEach(s => {
    s._deletable = 1 < realSolutions.length
  })

  saveCallback('solutions', newSolutions)
}

const updatePair = (property, value, index, solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  const formattedValue = 'score' === property ?
    parseFloat(value) :
    'ordered' === property ?
      Boolean(value) :
      value
  // 'index', 'property', 'value'
  // can update score feedback and ordered
  const solutionToUpdate = utils.getRealSolutionList(newSolutions)[index]
  solutionToUpdate[property] = formattedValue

  saveCallback('solutions', newSolutions)
}

const removePair = (leftId, rightId, solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  const idxToRemove = newSolutions.findIndex(s => s.itemIds[0] === leftId && s.itemIds[1] === rightId)
  newSolutions.splice(idxToRemove, 1)

  saveCallback('rows', newSolutions.filter(s => 0 < s.score).length)

  const realSolutions = utils.getRealSolutionList(newSolutions)
  realSolutions.forEach(s => {
    s._deletable = 1 < realSolutions.length
  })

  saveCallback('solutions', newSolutions)
}

const dropPairItem = (pairData, item, solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  // pairData = pair data + position of item dropped (0 / 1) + index (index of real solution)
  // item = dropped item
  const realSolutionList = utils.getRealSolutionList(newSolutions)
  const existingSolution = realSolutionList[pairData.index]
  existingSolution.itemIds[pairData.position] = item.id

  saveCallback('solutions', newSolutions)
}

const addItemCoordinates = (itemId, brotherId, coordinates, items, saveCallback) => {
  const newItems = cloneDeep(items)
  const itemToUpdate = newItems.find(i => i.id === itemId)

  if(itemToUpdate['coordinates']) {
    delete itemToUpdate.coordinates
  } else {
    itemToUpdate['coordinates'] = coordinates
    // remove coordinates from brother object
    if (-1 !== brotherId) {
      const brotherItem = newItems.find(i => i.id === brotherId)
      delete brotherItem.coordinates
    }
  }

  saveCallback('items', newItems)
}

const removeAllCoordinates = (items, saveCallback) => {
  const newItems = cloneDeep(items)
  newItems.map(item => delete item.coordinates)

  saveCallback('items', newItems)
}

let DropBox = props => props.connectDropTarget(
  <div className={classes(
    'pair-item-placeholder drop-placeholder placeholder-md placeholder-hover',
    {hover: props.isOver}
  )}>
    <span className="fa fa-fw fa-share fa-rotate-90" />
    {trans('set_drop_item', {}, 'quiz')}
  </div>
)

DropBox.propTypes = {
  connectDropTarget: T.func.isRequired,
  isOver: T.bool.isRequired,
  onDrop: T.func.isRequired,
  canDrop: T.bool.isRequired,
  object: T.object.isRequired
}

DropBox = makeDroppable(DropBox, 'ITEM')

class Pair extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <div className={classes(
        'pair answer-item',
        {'unexpected-answer' : this.props.pair.score < 1},
        {'expected-answer' : this.props.pair.score > 0}
      )}>
        <div className="text-fields">
          <div className="form-group">
            {this.props.pair.itemIds[0] === -1 ?
              <DropBox object={{pair:this.props.pair, position:0, index:this.props.index}} onDrop={this.props.onDrop} /> :
              <div className="pair-item">
                {this.props.showPins &&
                  <Button
                    id={`pair-${this.props.index}-${this.props.pair.itemIds[0]}-pin-me`}
                    className={classes(
                      'pull-right btn-link btn-pin-item',
                      {disabled: !utils.pairItemHasCoords(this.props.pair.itemIds[0], this.props.items, this.props.index)}
                    )}
                    type={CALLBACK_BUTTON}
                    icon="fa fa-fw fa-thumb-tack"
                    label={utils.pairItemHasCoords(this.props.pair.itemIds[0], this.props.items, this.props.index) ?
                      trans('pair_unpin_this_item', {}, 'quiz') :
                      trans('pair_pin_this_item', {}, 'quiz')
                    }
                    callback={() => this.props.onAddItemCoordinates(
                      this.props.pair.itemIds[0],
                      this.props.pair.itemIds[1],
                      [0, this.props.index]
                    )}
                    tooltip="top"
                  />
                }

                <div
                  className="item-content"
                  dangerouslySetInnerHTML={{__html: utils.getPairItemData(this.props.pair.itemIds[0], this.props.items)}}
                />
              </div>
            }

            {this.props.pair.itemIds[1] === -1 ?
              <DropBox object={{pair:this.props.pair, position:1, index:this.props.index}} onDrop={this.props.onDrop} /> :
              <div className="pair-item">
                {this.props.showPins &&
                  <Button
                    id={`pair-${this.props.index}-${this.props.pair.itemIds[1]}-pin-me`}
                    className={classes(
                      'pull-right btn-link btn-pin-item',
                      {disabled: !utils.pairItemHasCoords(this.props.pair.itemIds[1], this.props.items, this.props.index)}
                    )}
                    type={CALLBACK_BUTTON}
                    icon="fa fa-fw fa-thumb-tack"
                    label={utils.pairItemHasCoords(this.props.pair.itemIds[1], this.props.items, this.props.index) ?
                      trans('pair_unpin_this_item', {}, 'quiz') :
                      trans('pair_pin_this_item', {}, 'quiz')
                    }
                    callback={() => this.props.onAddItemCoordinates(
                      this.props.pair.itemIds[1],
                      this.props.pair.itemIds[0],
                      [1, this.props.index]
                    )}
                    tooltip="top"
                  />
                }

                <div
                  className="item-content"
                  dangerouslySetInnerHTML={{__html: utils.getPairItemData(this.props.pair.itemIds[1], this.props.items)}}
                />
              </div>
            }
          </div>

          {this.state.showFeedback &&
            <div className="feedback-container">
              <HtmlInput
                id={`${this.props.pair.itemIds[0]}-${this.props.pair.itemIds[1]}-feedback`}
                value={this.props.pair.feedback}
                onChange={(value) => this.props.onUpdate('feedback', value, this.props.index)}
              />
            </div>
          }

          <div className="checkbox">
            <label>
              <input
                type="checkbox"
                disabled={this.props.showPins || utils.pairItemHasCoords(this.props.pair.itemIds[1], this.props.items, this.props.index) || utils.pairItemHasCoords(this.props.pair.itemIds[0], this.props.items, this.props.index)}
                checked={this.props.pair.ordered || utils.pairItemHasCoords(this.props.pair.itemIds[1], this.props.items, this.props.index) || utils.pairItemHasCoords(this.props.pair.itemIds[0], this.props.items, this.props.index)}
                onChange={(e) => this.props.onUpdate('ordered', e.target.checked, this.props.index)}
              />
              {trans('pair_is_ordered', {}, 'quiz')}
            </label>
          </div>
        </div>

        <div className="right-controls">
          <input
            title={trans('score', {}, 'quiz')}
            type="number"
            className="form-control association-score"
            value={this.props.pair.score}
            onChange={(e) => this.props.onUpdate('score', e.target.value, this.props.index)}
          />

          <Button
            id={`ass-${this.props.pair.itemIds[0]}-${this.props.pair.itemIds[1]}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={trans('feedback_association_created', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`ass-${this.props.pair.itemIds[0]}-${this.props.pair.itemIds[1]}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            label={trans('delete', {}, 'actions')}
            disabled={!this.props.pair._deletable}
            callback={() => this.props.onDelete(this.props.pair.itemIds[0], this.props.pair.itemIds[1])}
            tooltip="top"
            dangerous={true}
          />
        </div>
      </div>
    )
  }
}

Pair.propTypes = {
  pair: T.object.isRequired,
  onDrop: T.func.isRequired,
  index: T.number.isRequired,
  showPins: T.bool.isRequired,
  items: T.arrayOf(T.object).isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired,
  onAddItemCoordinates: T.func.isRequired
}

class PairList extends Component {
  constructor(props) {
    super(props)
    this.state = {
      pinIsAllowed: props.items.filter(item => item.hasOwnProperty('coordinates')).length > 0
    }
  }

  /**
   * handle item drop
   * @var {source} source (source.item is the object that has been dropped)
   * @var {target} target (target.object is the pair and the position where the item has been dropped  (0 / 1) and the solution index)
   */
  onItemDrop(source, target){
    // target.object is the pair and the position where the item has been dropped  (0 / 1) and the solution index
    // source.item is the object that has been dropped
    if (utils.canAddSolution(this.props.solutions, target.object, source.item)) {
      dropPairItem(target.object, source.item, this.props.solutions, this.props.onChange)
    }
  }

  handlePinnableChange(checked) {
    this.setState({pinIsAllowed: !this.state.pinIsAllowed})
    if (!checked) {
      removeAllCoordinates(this.props.items, this.props.onChange)
    }
  }

  render(){
    return (
      <div className="pairs">
        <div className="checkbox">
          <label>
            <input
              type="checkbox"
              checked={this.state.pinIsAllowed}
              onChange={(e) => this.handlePinnableChange(e.target.checked)}
            />
            {trans('pair_allow_pin_function', {}, 'quiz')}
          </label>
        </div>

        <hr />

        <ul>
          {utils.getRealSolutionList(this.props.solutions).map((pair, index) =>
            <li key={`pair-${index}`}>
              <Pair
                pair={pair}
                onDrop={(source, target) => this.onItemDrop(source, target)}
                onUpdate={(property, value, index) => updatePair(property, value, index, this.props.solutions, this.props.onChange)}
                onDelete={(leftId, rightId) => removePair(leftId, rightId, this.props.solutions, this.props.onChange)}
                onAddItemCoordinates={(itemId, brotherId, coordinates) => addItemCoordinates(itemId, brotherId, coordinates, this.props.items, this.props.onChange)}
                index={index}
                showPins={this.state.pinIsAllowed}
                items={this.props.items}
              />
            </li>
          )}
        </ul>

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-block"
          icon="fa fa-fw fa-plus"
          label={trans('pair_add_pair', {}, 'quiz')}
          callback={() => addPair(this.props.solutions, this.props.onChange)}
        />
      </div>
    )
  }
}

PairList.propTypes = {
  items: T.arrayOf(T.object).isRequired,
  solutions: T.arrayOf(T.object).isRequired,
  onChange: T.func.isRequired
}

class Odd extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFeedback: false
    }
  }

  render(){
    return (
      <div className="answer-item item unexpected-answer">
        <div className="text-fields">
          <HtmlInput
            id={`odd-${this.props.odd.id}-data`}
            value={this.props.odd.data}
            onChange={(value) => this.props.onUpdate('data', value)}
            minRows={1}
          />
          {this.state.showFeedback &&
            <div className="feedback-container">
              <HtmlInput
                id={`odd-${this.props.odd.id}-feedback`}
                value={this.props.solution.feedback}
                onChange={(value) => this.props.onUpdate('feedback', value)}
              />
            </div>
          }
        </div>

        <div className="right-controls">
          <input
            title={trans('score', {}, 'quiz')}
            type="number"
            max="0"
            className="form-control odd-score"
            value={this.props.solution.score}
            onChange={(e) => this.props.onUpdate('score', e.target.value)}
          />

          <Button
            id={`odd-${this.props.odd.id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={trans('feedback_answer_check', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`odd-${this.props.odd.id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            label={trans('delete', {}, 'actions')}
            callback={() => this.props.onDelete()}
            tooltip="top"
            dangerous={true}
          />
        </div>
      </div>
    )
  }
}

Odd.propTypes = {
  odd: T.object.isRequired,
  solution: T.object.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

const OddList= props =>
  <div className="odd-list">
    <ul>
      {utils.getOddlist(props.items, props.solutions).map((oddItem, index) =>
        <li key={`odd-${index}-${oddItem.id}`}>
          <Odd
            odd={oddItem}
            solution={utils.getOddSolution(oddItem, props.solutions)}
            onUpdate={(property, value) => updateItem(property, value, oddItem.id, props.items, props.solutions, true, props.onChange)}
            onDelete={() => removeItem(oddItem.id, props.items, props.solutions, true, props.onChange)}
          />
        </li>
      )}
    </ul>

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('set_add_odd', {}, 'quiz')}
      callback={() => addItem(props.items, props.solutions, true, props.onChange)}
    />
  </div>

OddList.propTypes = {
  items: T.arrayOf(T.object).isRequired,
  solutions: T.arrayOf(T.object).isRequired,
  onChange: T.func.isRequired
}

let Item = props =>
  <div className="answer-item item">
    <div className="text-fields">
      <HtmlInput
        id={`${props.item.id}-data`}
        value={props.item.data}
        onChange={(value) => props.onUpdate('data', value)}
        minRows={1}
      />
    </div>

    <div className="right-controls">
      <Button
        id={`set-item-${props.item.id}-delete`}
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash-o"
        label={trans('delete', {}, 'actions')}
        disabled={!props.item._deletable}
        callback={() => props.onDelete()}
        tooltip="top"
        dangerous={true}
      />

      {props.connectDragSource(
        <div>
          <OverlayTrigger
            placement="top"
            overlay={
              <Tooltip id={`item-${props.item.id}-drag`}>{trans('move')}</Tooltip>
            }
          >
              <span
                role="button"
                title={trans('move')}
                draggable="true"
                className="btn-link default drag-handle"
              >
                <span className="fa fa-fw fa-arrows" />
              </span>
          </OverlayTrigger>
        </div>
      )}
    </div>
  </div>

Item.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

Item = makeDraggable(Item, 'ITEM', PairItemDragPreview)

const ItemList = props =>
  <div className="item-list">
    <ul>
      {utils.getRealItemlist(props.items, props.solutions).map((item) =>
        <li key={item.id}>
          <Item
            item={item}
            onUpdate={(property, value) => updateItem(property, value, item.id, props.items, props.solutions, false, props.onChange)}
            onDelete={() => removeItem(item.id, props.items, props.solutions, false, props.onChange)}
          />
        </li>
      )}
    </ul>

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('set_add_item', {}, 'quiz')}
      callback={() => addItem(props.items, props.solutions, false, props.onChange)}
    />
  </div>

ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired,
  solutions: T.arrayOf(T.object).isRequired,
  onChange: T.func.isRequired
}

const PairEditor = props =>
  <FormData
    className="pair-item pair-editor"
    embedded={true}
    name={props.formName}
    dataPart={props.path}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'penalty',
            label: trans('editor_penalty_label', {}, 'quiz'),
            type: 'number',
            required: true,
            options: {
              min: 0
            }
          }, {
            name: 'pairs',
            label: trans('answers', {}, 'quiz'),
            required: true,
            render: (pairItem) => {
              const decoratedSolutions = cloneDeep(pairItem.solutions)
              const realSolutions = utils.getRealSolutionList(decoratedSolutions)
              decoratedSolutions.forEach(s => s._deletable = 1 < realSolutions.length)

              const decoratedItems = cloneDeep(pairItem.items)
              const itemDeletable = 2 < utils.getRealItemlist(decoratedItems, decoratedSolutions).length
              decoratedItems.forEach(el => el._deletable = itemDeletable)

              const Pair = (
                <div className="row pair-items">
                  <div className="col-md-5 col-sm-5 items-col">
                    <ItemList
                      solutions={decoratedSolutions}
                      items={decoratedItems}
                      onChange={props.update}
                    />
                    <hr/>
                    <OddList
                      solutions={decoratedSolutions}
                      items={decoratedItems}
                      onChange={props.update}
                    />
                  </div>
                  <div className="col-md-7 col-sm-7 pairs-col">
                    <PairList
                      solutions={decoratedSolutions}
                      items={decoratedItems}
                      onChange={props.update}
                    />
                  </div>
                </div>
              )

              return Pair
            }
          }, {
            name: 'random',
            label: trans('shuffle_answers', {}, 'quiz'),
            help: [
              trans('shuffle_answers_help', {}, 'quiz'),
              trans('shuffle_answers_results_help', {}, 'quiz')
            ],
            type: 'boolean'
          }
        ]
      }
    ]}
  />

implementPropTypes(PairEditor, ItemEditorTypes, {
  item: T.shape(PairItemType.propTypes).isRequired
})

export {
  PairEditor
}
