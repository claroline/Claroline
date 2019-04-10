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
import {FormGroup} from '#/main/app/content/form/components/group'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {emptyAnswer} from '#/plugin/exo/items/utils'
import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {SetItem as SetItemType} from '#/plugin/exo/items/set/prop-types'
import {utils} from '#/plugin/exo/items/set/utils'
import {SetItemDragPreview} from '#/plugin/exo/items/set/components/set-item-drag-preview.jsx'

const addItem = (items, saveCallback) => {
  const newItems = cloneDeep(items)

  const newItem = emptyAnswer()
  newItems.push(newItem)

  saveCallback('items', newItems)

  return newItem
}

const addOdd = (items, solutions, saveCallback) => {
  const newItem = addItem(items, saveCallback)

  const newSolutions = cloneDeep(solutions)
  newSolutions.odd.push({
    itemId: newItem.id,
    score: 0,
    feedback: ''
  })

  saveCallback('solutions', newSolutions)
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

const removeItem = (itemId, items, solutions, saveCallback) => {
  const newItems = cloneDeep(items)
  const index = newItems.findIndex(i => i.id === itemId)

  if (-1 < index) {
    newItems.splice(index, 1)
    const newSolutions = cloneDeep(solutions)

    // remove item from solution odds
    if (newSolutions.odd) {
      newSolutions.odd.forEach((odd) => {
        if (odd.itemId === itemId){
          const idx = newSolutions.odd.findIndex(o => o.itemId === itemId)
          newSolutions.odd.splice(idx, 1)
        }
      })
    }

    if (newSolutions.associations) {
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
  newSets.push(emptyAnswer())

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
    feedback: ''
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
    <span className="fa fa-fw fa-share fa-rotate-90 icon-with-text-right" />
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
      <div className={classes('association set-answer-item answer-item', {
        'expected-answer' : this.props.association.score > 0,
        'unexpected-answer': this.props.association.score <= 0
      })}>
        <div className="text-fields">
          <HtmlText className="form-control">
            {this.props.association._itemData}
          </HtmlText>

          {this.state.showFeedback &&
            <HtmlInput
              id={`${this.props.association.itemId}-${this.props.association.setId}-feedback`}
              className="feedback-control"
              value={this.props.association.feedback}
              onChange={(value) => this.props.onUpdate('feedback', value, this.props.association.setId, this.props.association.itemId)}
            />
          }
        </div>

        <div className="right-controls">
          <input
            title={trans('score', {}, 'quiz')}
            type="number"
            className="form-control score"
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
            dangerous={true}
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
  <div className="set">
    <div className="set-heading">
      <div className="text-fields">
        <HtmlInput
          id={`set-${props.set.id}-data`}
          value={props.set.data}
          placeholder={trans('set', {number: props.index + 1}, 'quiz')}
          onChange={(value) => props.onUpdate('data', value)}
          minRows={1}
        />
      </div>

      <div className="right-controls">
        <Button
          id={`set-${props.set.id}-delete`}
          className="btn-link"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-trash-o"
          label={trans('delete', {}, 'actions')}
          disabled={!props.deletable}
          callback={() => props.onDelete()}
          tooltip="top"
          dangerous={true}
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
  index: T.number.isRequired,
  set: T.object.isRequired,
  deletable: T.bool.isRequired,
  associations: T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired,

  onChange: T.func.isRequired,
  onDrop: T.func.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

const SetList = (props) =>
  <FormGroup
    id="item-sets"
    label={trans('sets', {}, 'quiz')}
  >
    <ul>
      {props.sets.map((set, setIndex) =>
        <li key={`set-id-${set.id}`}>
          <Set
            index={setIndex}
            set={set}
            deletable={1 < props.sets.length}
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

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('set_add_set', {}, 'quiz')}
      callback={() => addSet(props.sets, props.onChange)}
    />
  </FormGroup>

SetList.propTypes = {
  sets: T.arrayOf(T.object).isRequired,
  solutions: T.shape({
    associations: T.arrayOf(T.object).isRequired,
    odds: T.arrayOf(T.object)
  }).isRequired,
  onChange: T.func.isRequired
}

let Item = (props) =>
  <div className="set-answer-item answer-item">
    <div className="text-fields">
      <HtmlInput
        id={`item-${props.item.id}-data`}
        value={props.item.data}
        onChange={(value) => props.onUpdate('data', value)}
        placeholder={trans('item', {number: props.index + 1}, 'quiz')}
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
        disabled={!props.deletable}
        callback={props.onDelete}
        tooltip="top"
        dangerous={true}
      />

      {props.connectDragSource(
        <div>
          <OverlayTrigger
            placement="top"
            overlay={
              <Tooltip id={`set-item-${props.item.id}-drag`}>{trans('move')}</Tooltip>
            }
          >
            <span
              title={trans('move', {}, 'actions')}
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
  index: T.number.isRequired,
  item: T.object.isRequired,
  deletable: T.bool.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired,
  connectDragSource: T.func.isRequired
}

Item = makeDraggable(Item, 'ITEM', SetItemDragPreview)

const ItemList = (props) =>
  <FormGroup
    id="item-items"
    label={trans('items', {}, 'quiz')}
  >
    <ul>
      {props.items.map((item, itemIndex) =>
        <li key={item.id}>
          <Item
            index={itemIndex}
            item={item}
            deletable={1 < props.items.length}
            onUpdate={(property, value) => updateItem(property, value, item.id, props.items, props.solutions, false, props.onChange)}
            onDelete={() => props.delete(item.id)}
          />
        </li>
      )}
    </ul>

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('set_add_item', {}, 'quiz')}
      callback={props.add}
    />
  </FormGroup>

ItemList.propTypes = {
  items: T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired,
  onChange: T.func.isRequired,

  add: T.func.isRequired,
  delete: T.func.isRequired
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
      <div className={classes('set-answer-item answer-item', {
        'expected-answer' : this.props.solution.score > 0,
        'unexpected-answer': this.props.solution.score < 1
      })}>
        <div className="text-fields">
          <HtmlInput
            id={`odd-${this.props.odd.id}-data`}
            value={this.props.odd.data}
            placeholder={trans('odd', {number: this.props.index + 1}, 'quiz')}
            onChange={(value) => this.props.onUpdate('data', value)}
            minRows={1}
          />

          {this.state.showFeedback &&
            <HtmlInput
              id={`odd-${this.props.odd.id}-feedback`}
              className="feedback-control"
              value={this.props.solution.feedback}
              onChange={(value) => this.props.onUpdate('feedback', value)}
            />
          }
        </div>

        <div className="right-controls">
          <input
            title={trans('score', {}, 'quiz')}
            type="number"
            max={0}
            className="form-control score"
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
            callback={this.props.onDelete}
            tooltip="top"
            dangerous={true}
          />
        </div>
      </div>
    )
  }
}

Odd.propTypes = {
  index: T.number.isRequired,
  odd: T.object.isRequired,
  solution: T.object.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

const OddList = (props) =>
  <FormGroup
    id="item-odds"
    label={trans('odds', {}, 'quiz')}
    optional={true}
  >
    {0 === props.items.length &&
      <div className="no-item-info">{trans('no_odd_info', {}, 'quiz')}</div>
    }

    {0 < props.items.length &&
      <ul>
        {props.items.map((oddItem, oddIndex) =>
          <li key={oddItem.id}>
            <Odd
              index={oddIndex}
              odd={oddItem}
              solution={props.solutions.odd.find(o => o.itemId === oddItem.id)}
              onUpdate={(property, value) => updateItem(property, value, oddItem.id, props.items, props.solutions, true, props.onChange)}
              onDelete={() => props.delete(oddItem.id)}
            />
          </li>
        )}
      </ul>
    }

    <Button
      type={CALLBACK_BUTTON}
      className="btn btn-block"
      icon="fa fa-fw fa-plus"
      label={trans('set_add_odd', {}, 'quiz')}
      callback={props.add}
    />
  </FormGroup>

OddList.propTypes = {
  items: T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired,
  onChange: T.func.isRequired,

  add: T.func.isRequired,
  delete: T.func.isRequired
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
            className: 'form-last',
            name: 'sets',
            label: trans('answers', {}, 'quiz'),
            hideLabel: true,
            required: true,
            render: (setItem) => {
              const Set = (
                <div className="row">
                  <div className="items-col col-md-5 col-sm-5 col-xs-5">
                    <ItemList
                      items={setItem.items.filter(item => !utils.isOdd(item.id, setItem.solutions))}
                      solutions={setItem.solutions}
                      onChange={props.update}

                      add={() => addItem(setItem.items, props.update)}
                      delete={(itemId) => removeItem(itemId, setItem.items, setItem.solutions, props.update)}
                    />

                    <OddList
                      items={setItem.items.filter(item => utils.isOdd(item.id, setItem.solutions))}
                      solutions={setItem.solutions}
                      onChange={props.update}

                      add={() => addOdd(setItem.items, setItem.solutions, props.update)}
                      delete={(itemId) => removeItem(itemId, setItem.items, setItem.solutions, props.update)}
                    />
                  </div>

                  <div className="sets-col col-md-7 col-sm-7 col-xs-7">
                    <SetList
                      sets={setItem.sets}
                      solutions={setItem.solutions}
                      onChange={props.update}
                    />
                  </div>
                </div>
              )

              return Set
            }
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
            label: trans('editor_penalty_label', {}, 'quiz'),
            type: 'number',
            required: true,
            options: {
              min: 0
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(SetEditor, ItemEditorType, {
  item: T.shape(SetItemType.propTypes).isRequired
})

export {
  SetEditor
}
