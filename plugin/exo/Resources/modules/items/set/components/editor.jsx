import React, {Component} from 'react'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'

import {makeId} from '#/main/core/scaffolding/id'
import {Textarea} from '#/main/core/layout/form/components/field/textarea'

import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {SetItem as SetItemType} from '#/plugin/exo/items/set/prop-types'
import {SetItemDragPreview} from '#/plugin/exo/items/set/components/set-item-drag-preview.jsx'

const addItem = (items, solutions, isOdd, saveCallback) => {
  const newItems = cloneDeep(items)
  const newSolutions = cloneDeep(solutions)
  const id = makeId()
  newItems.push({
    id: id,
    type: 'text/html',
    data: ''
  })

  if (isOdd) {
    newSolutions.odd.push({
      itemId: id,
      score: 0,
      feedback: ''
    })
    saveCallback('solutions', newSolutions)
  }

  // consider items that are not in solutions.odd
  const itemDeletable = 1 < newItems.filter(i => undefined === newSolutions.odd.find(o => o.itemId === i.id)).length
  newItems.filter(i => undefined === newSolutions.odd.find(o => o.itemId === i.id)).forEach(i => i._deletable = itemDeletable)

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
        const oddSolution = newSolutions.odd.find(o => o.itemId === itemId)
        oddSolution[property] = formattedValue
        saveCallback('solutions', newSolutions)
      }
    } else {
      itemToUpdate[property] = formattedValue

      // update associations item data
      const newSolutions = cloneDeep(solutions)
      newSolutions.associations.map((ass) => {
        if(ass.itemId === itemId){
          ass._itemData = value
        }
      })
      saveCallback('solutions', newSolutions)
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
      // remove item from solution odds
      newSolutions.odd.forEach((odd) => {
        if (odd.itemId === itemId){
          const idx = newSolutions.odd.findIndex(o => o.itemId === itemId)
          newSolutions.odd.splice(idx, 1)
        }
      })
    } else {
      // consider items that are not in solutions.odd
      const itemDeletable = 1 < newItems.filter(i => undefined === newSolutions.odd.find(o => o.itemId === i.id)).length
      newItems.filter(i => undefined === newSolutions.odd.find(o => o.itemId === i.id)).forEach(i => i._deletable = itemDeletable)
      // remove item from solution associations
      newSolutions.associations.forEach((ass) => {
        if (ass.itemId === itemId){
          const idx = newSolutions.associations.findIndex(a => a.itemId === itemId)
          newSolutions.associations.splice(idx, 1)
        }
      })
    }

    saveCallback('items', newItems)
    saveCallback('solutions', newSolutions)
  }
}

const addSet = (sets, saveCallback) => {
  const newSets = cloneDeep(sets)
  newSets.push({
    id: makeId(),
    type: 'text/html',
    data: ''
  })
  newSets.forEach(s => s._deletable = 1 < newSets.length)

  saveCallback('sets', newSets)
}

const updateSet = (property, value, setId, sets, saveCallback) => {
  const newSets = cloneDeep(sets)
  const toUpdate = newSets.find(s => s.id === setId)
  toUpdate[property] = value

  saveCallback('sets', newSets)
}

const removeSet = (setId, sets, solutions, saveCallback) => {
  const newSets = cloneDeep(sets)
  const newSolutions = cloneDeep(solutions)
  const index = newSets.findIndex(s => s.id === setId)
  newSets.splice(index, 1)
  newSets.forEach(s => s._deletable = 1 < newSets.length)
  // remove set from solution
  newSolutions.associations.forEach((ass, idx) => {
    if (ass.setId === setId){
      newSolutions.associations.splice(idx, 1)
    }
  })

  saveCallback('sets', newSets)
  saveCallback('solutions', newSolutions)
}

const addAssociation = (setId, itemId, itemData, solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  newSolutions.associations.push({
    itemId: itemId,
    setId: setId,
    score: 1,
    feedback: '',
    _itemData: itemData
  })

  saveCallback('solutions', newSolutions)
}

const updateAssociation = (property, value, setId, itemId, solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  const formattedValue = 'score' === property ? parseFloat(value) : value
  const association = newSolutions.associations.find(a => a.setId === setId && a.itemId === itemId)
  association[property] = formattedValue


  saveCallback('solutions', newSolutions)
}

const removeAssociation = (setId, itemId, solutions, saveCallback) => {
  const newSolutions = cloneDeep(solutions)
  const index = newSolutions.associations.findIndex(a => a.itemId === itemId && a.setId === setId)

  if (-1 < index) {
    newSolutions.associations.splice(index, 1)
    saveCallback('solutions', newSolutions)
  }
}

/**
 * handle item drop
 * @var {source} dropped item (item)
 * @var {target} target item (set)
 * @var solutions
 * @var saveCallback
 */
const dropItem = (source, target, solutions, saveCallback) => {
  // add solution (check the item is not already inside before adding it)
  if (undefined === solutions.associations.find(a => a.setId === target.object.id && a.itemId === source.item.id)){
    addAssociation(target.object.id, source.item.id, source.item.data, solutions, saveCallback)
  }
}

let DropBox = props => props.connectDropTarget(
  <div className={classes('set-drop-placeholder', {
    hover: props.isOver
  })}>
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

class Association extends Component {
  constructor(props) {
    super(props)

    this.state = {
      showFeedback: false
    }
  }

  render(){
    return (
      <div className={classes('association answer-item', {'expected-answer' : this.props.association.score > 0}, {'unexpected-answer': this.props.association.score <= 0})}>
        <div className="text-fields">
          <div className="association-data" dangerouslySetInnerHTML={{__html: this.props.association._itemData}} />

          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`${this.props.association.itemId}-${this.props.association.setId}-feedback`}
                value={this.props.association.feedback}
                onChange={(value) => this.props.onUpdate('feedback', value, this.props.association.setId, this.props.association.itemId)}
              />
            </div>
          }
        </div>

        <div className="right-controls">
          <input
            title={trans('score', {}, 'quiz')}
            type="number"
            className="form-control association-score"
            value={this.props.association.score}
            onChange={(e) => this.props.onUpdate('score', e.target.value, this.props.association.setId, this.props.association.itemId)}
          />

          <Button
            id={`ass-${this.props.association.itemId}-${this.props.association.setId}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={trans('feedback_association_created', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`ass-${this.props.association.itemId}-${this.props.association.setId}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            label={trans('delete', {}, 'actions')}
            callback={() => this.props.onDelete(this.props.association.setId, this.props.association.itemId)}
            tooltip="top"
          />
        </div>
      </div>
    )
  }
}

Association.propTypes = {
  association: T.object.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

const Set = (props) =>
  <div className="set answer-item">
    <div className="set-heading">
      <div className="text-fields">
        <Textarea
          id={`${props.set.id}-data`}
          value={props.set.data}
          onChange={(value) => props.onUpdate('data', value)}
        />
      </div>

      <div className="right-controls">
        <Button
          id={`set-${props.set.id}-delete`}
          className="btn-link"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-trash-o"
          label={trans('delete', {}, 'actions')}
          disabled={!props.set._deletable}
          callback={() => props.onDelete()}
          tooltip="top"
        />
      </div>
    </div>

    <ul>
      {props.associations.map(ass =>
        <li key={`${ass.itemId}-${ass.setId}`}>
          <Association
            association={ass}
            onUpdate={(property, value, setId, itemId) => updateAssociation(property, value, setId, itemId, props.solutions, props.onChange)}
            onDelete={(setId, itemId) => removeAssociation(setId, itemId, props.solutions, props.onChange)}
          />
        </li>
      )}
    </ul>

    <DropBox object={props.set} onDrop={props.onDrop} />
  </div>

Set.propTypes = {
  set: T.object.isRequired,
  associations: T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired,
  onChange: T.func.isRequired,
  onDrop: T.func.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

const SetList = (props) =>
  <div className="sets">
    <ul>
      {props.sets.map((set) =>
        <li key={`set-id-${set.id}`}>
          <Set
            set={set}
            associations={props.solutions.associations.filter(association => association.setId === set.id) || []}
            solutions={props.solutions}
            onChange={props.onChange}
            onDrop={(source, target) => dropItem(source, target, props.solutions, props.onChange)}
            onUpdate={(property, value) => updateSet(property, value, set.id, props.sets, props.onChange)}
            onDelete={() => removeSet(set.id, props.sets, props.solutions, props.onChange)}
          />
        </li>
      )}
    </ul>
    <div className="footer">
      <button
        type="button"
        className="btn btn-default"
        onClick={() => addSet(props.sets, props.onChange)}
      >
        <span className="fa fa-fw fa-plus"/>
        {trans('set_add_set', {}, 'quiz')}
      </button>
    </div>
  </div>

SetList.propTypes = {
  sets: T.arrayOf(T.object).isRequired,
  solutions: T.shape({
    associations: T.arrayOf(T.object).isRequired,
    odds: T.arrayOf(T.object)
  }).isRequired,
  onChange: T.func.isRequired
}

let Item = (props) =>
  <div className="set-item answer-item">
    <div className="text-fields">
      <Textarea
        id={`${props.item.id}-data`}
        value={props.item.data}
        onChange={(value) => props.onUpdate('data', value)}
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
      />

      {props.connectDragSource(
        <div>
          <OverlayTrigger
            placement="top"
            overlay={
              <Tooltip id={`set-item-${props.item.id}-drag`}>{trans('move')}</Tooltip>
            }>
            <span
              title={trans('move')}
              draggable="true"
              className={classes(
                'tooltiped-button',
                'btn',
                'btn-link-default',
                'fa fa-fw',
                'fa-arrows',
                'drag-handle'
              )}
            />
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

Item = makeDraggable(
  Item,
  'ITEM',
  SetItemDragPreview
)

const ItemList = (props) =>
  <div className="item-list">
    <ul>
      {props.items.filter(i => undefined === props.solutions.odd.find(o => o.itemId === i.id)).map((item) =>
        <li key={item.id}>
          <Item
            item={item}
            onUpdate={(property, value) => updateItem(property, value, item.id, props.items, props.solutions, false, props.onChange)}
            onDelete={() => removeItem(item.id, props.items, props.solutions, false, props.onChange)}
          />
        </li>
      )}
    </ul>
    <div className="footer text-center">
      <button
        type="button"
        className="btn btn-default"
        onClick={() => addItem(props.items, props.solutions, false, props.onChange)}
      >
        <span className="fa fa-fw fa-plus"/>
        {trans('set_add_item', {}, 'quiz')}
      </button>
    </div>
  </div>

ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired,
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
      <div className={classes('set-item answer-item', {'expected-answer' : this.props.solution.score > 0}, {'unexpected-answer': this.props.solution.score < 1})}>
        <div className="text-fields">
          <Textarea
            id={`odd-${this.props.odd.id}-data`}
            value={this.props.odd.data}
            onChange={(value) => this.props.onUpdate('data', value)}
          />
          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
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
            label={trans('feedback', {}, 'quiz')}
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

const OddList = (props) =>
  <div className="odd-list">
    <ul>
      {props.items.filter(item => undefined !== props.solutions.odd.find(o => o.itemId === item.id)).map((oddItem) =>
        <li key={oddItem.id}>
          <Odd
            odd={oddItem}
            solution={props.solutions.odd.find(o => o.itemId === oddItem.id)}
            onUpdate={(property, value) => updateItem(property, value, oddItem.id, props.items, props.solutions, true, props.onChange)}
            onDelete={() => removeItem(oddItem.id, props.items, props.solutions, true, props.onChange)}
          />
        </li>
      )}
    </ul>
    <div className="footer">
      <button
        type="button"
        className="btn btn-default"
        onClick={() => addItem(props.items, props.solutions, true, props.onChange)}
      >
        <span className="fa fa-fw fa-plus"/>
        {trans('set_add_odd', {}, 'quiz')}
      </button>
    </div>
  </div>

OddList.propTypes = {
  items: T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired,
  onChange: T.func.isRequired
}

const SetEditor = (props) =>
  <FormData
    className="set-item set-editor"
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
            name: 'random',
            label: trans('set_shuffle_labels_and_proposals', {}, 'quiz'),
            type: 'boolean'
          }, {
            name: 'sets',
            label: trans('sets', {}, 'quiz'),
            hideLabel: true,
            required: true,
            render: (setItem, setErrors) => {
              return (
                <div className="set-items row">
                  <div className="col-md-5 col-sm-5 col-xs-5">
                    <ItemList
                      items={setItem.items}
                      solutions={setItem.solutions}
                      onChange={props.update}
                    />
                    <hr className="item-content-separator" />

                    <OddList
                      items={setItem.items}
                      solutions={setItem.solutions}
                      onChange={props.update}
                    />
                  </div>

                  <div className="col-md-7 col-sm-7 col-xs-7">
                    <SetList
                      sets={setItem.sets}
                      solutions={setItem.solutions}
                      onChange={props.update}
                    />
                  </div>
                </div>
              )
            },
            validate: (setItem) => {
              return undefined
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(SetEditor, ItemEditorType, {
  item: T.shape(SetItemType.propTypes).isRequired
})

export {SetEditor}
