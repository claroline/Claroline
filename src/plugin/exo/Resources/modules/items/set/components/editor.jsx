import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ContentHtml} from '#/main/app/content/components/html'
import {HtmlInput} from '#/main/app/data/types/html/components/input'

import {SCORE_SUM} from '#/plugin/exo/quiz/enums'
import {emptyAnswer} from '#/plugin/exo/items/utils'
import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {ItemEditor as ItemEditorType} from '#/plugin/exo/items/prop-types'
import {SetItem as SetItemType} from '#/plugin/exo/items/set/prop-types'
import {utils} from '#/plugin/exo/items/set/utils'
import {SetItemDragPreview} from '#/plugin/exo/items/set/components/set-item-drag-preview'

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
  const formattedValue = 'score' === property ? parseFloat(value) : value

  const itemIndex = items.findIndex(i => i.id === itemId)
  if (-1 !== itemIndex) {
    const itemToUpdate = cloneDeep(items[itemIndex])

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

    saveCallback(`items[${itemIndex}]`, itemToUpdate)
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

const removeSet = (setId, sets, associations, saveCallback) => {
  const newSets = cloneDeep(sets)
  const newAssociations = cloneDeep(associations)
  const index = newSets.findIndex(s => s.id === setId)
  newSets.splice(index, 1)
  // remove set from solution
  newAssociations.forEach((ass, idx) => {
    if (ass.setId === setId){
      newAssociations.splice(idx, 1)
    }
  })

  saveCallback('sets', newSets)
  saveCallback('solutions.associations', newAssociations)
}

const addAssociation = (setId, itemId, itemData, associations, saveCallback) => {
  const newAssociations = cloneDeep(associations)
  newAssociations.push({
    itemId: itemId,
    setId: setId,
    score: 1,
    feedback: ''
  })

  saveCallback('solutions.associations', newAssociations)
}

const updateAssociation = (property, value, setId, itemId, associations, saveCallback) => {
  const newAssociations = cloneDeep(associations)
  const formattedValue = 'score' === property ? parseFloat(value) : value
  const association = newAssociations.find(a => a.setId === setId && a.itemId === itemId)
  association[property] = formattedValue

  saveCallback('solutions.associations', newAssociations)
}

const removeAssociation = (setId, itemId, associations, saveCallback) => {
  const newAssociations = cloneDeep(associations)
  const index = newAssociations.findIndex(a => a.itemId === itemId && a.setId === setId)

  if (-1 < index) {
    newAssociations.splice(index, 1)
    saveCallback('solutions.associations', newAssociations)
  }
}

/**
 * handle item drop
 */
const dropItem = (source, target, associations, saveCallback) => {
  // add solution (check the item is not already inside before adding it)
  if (undefined === associations.find(a => a.setId === target.object.id && a.itemId === source.item.id)){
    addAssociation(target.object.id, source.item.id, source.item.data, associations, saveCallback)
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
      <div className={classes('association set-answer-item answer-item', this.props.hasExpectedAnswers && {
        'expected-answer' : this.props.association.score > 0,
        'unexpected-answer': this.props.association.score <= 0
      })}>
        <div className="text-fields">
          {this.props.association._itemData &&
            <ContentHtml className="form-control">
              {this.props.association._itemData}
            </ContentHtml>
          }

          {!this.props.association._itemData &&
            <span className="form-control input-placeholder">
              {trans('item', {number: this.props.association._itemIndex + 1}, 'quiz')}
            </span>
          }

          {this.state.showFeedback &&
            <HtmlInput
              id={`association-${this.props.association.itemId}-${this.props.association.setId}-feedback`}
              className="feedback-control"
              value={this.props.association.feedback}
              onChange={(value) => this.props.onUpdate('feedback', value, this.props.association.setId, this.props.association.itemId)}
            />
          }
        </div>

        <div className="right-controls">
          {this.props.hasExpectedAnswers && this.props.hasScore &&
            <input
              title={trans('score', {}, 'quiz')}
              type="number"
              className="form-control score"
              value={this.props.association.score}
              onChange={(e) => this.props.onUpdate('score', e.target.value, this.props.association.setId, this.props.association.itemId)}
            />
          }

          {this.props.hasExpectedAnswers && !this.props.hasScore &&
            <input
              title={trans('score', {}, 'quiz')}
              type="checkbox"
              checked={0 < this.props.association.score}
              onChange={(e) => this.props.onUpdate('score', e.target.checked ? 1 : 0, this.props.association.setId, this.props.association.itemId)}
            />
          }

          <Button
            id={`ass-${this.props.association.itemId}-${this.props.association.setId}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments"
            label={trans('feedback_association_created', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`ass-${this.props.association.itemId}-${this.props.association.setId}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash"
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
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
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
          icon="fa fa-fw fa-trash"
          label={trans('delete', {}, 'actions')}
          disabled={!props.deletable}
          callback={props.onDelete}
          tooltip="top"
          dangerous={true}
        />
      </div>
    </div>

    <ul>
      {props.associations.filter(association => association.setId === props.set.id).map(ass =>
        <li key={`${ass.itemId}-${ass.setId}`}>
          <Association
            association={ass}
            onUpdate={(property, value, setId, itemId) => updateAssociation(property, value, setId, itemId, props.associations, props.onChange)}
            onDelete={(setId, itemId) => removeAssociation(setId, itemId, props.associations, props.onChange)}
            hasScore={props.hasScore}
            hasExpectedAnswers={props.hasExpectedAnswers}
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
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,

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
            associations={props.associations || []}
            hasScore={props.hasScore}
            hasExpectedAnswers={props.hasExpectedAnswers}
            onChange={props.onChange}
            onDrop={(source, target) => dropItem(source, target, props.associations, props.onChange)}
            onUpdate={(property, value) => updateSet(property, value, set.id, props.sets, props.onChange)}
            onDelete={() => removeSet(set.id, props.sets, props.associations, props.onChange)}
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
  associations: T.arrayOf(T.object).isRequired,
  onChange: T.func.isRequired,
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired
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
        icon="fa fa-fw fa-trash"
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
              <Tooltip id={`set-item-${props.item.id}-drag`}>{trans('move', {}, 'actions')}</Tooltip>
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
      {props.items
        .filter(item => !utils.isOdd(item.id, props.solutions))
        .map((item, itemIndex) =>
          <li key={item.id}>
            <Item
              index={itemIndex}
              item={item}
              deletable={1 < props.items.length}
              onUpdate={(property, value) => updateItem(property, value, item.id, props.items, props.solutions, false, props.onChange)}
              onDelete={() => props.delete(item.id)}
            />
          </li>
        )
      }
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
      <div className={classes('set-answer-item answer-item', this.props.hasExpectedAnswers && {
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
          {this.props.hasExpectedAnswers && this.props.hasScore &&
            <input
              title={trans('score', {}, 'quiz')}
              type="number"
              max={0}
              className="form-control score"
              value={this.props.solution.score}
              onChange={(e) => this.props.onUpdate('score', e.target.value)}
            />
          }

          <Button
            id={`odd-${this.props.odd.id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments"
            label={trans('feedback', {}, 'quiz')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`odd-${this.props.odd.id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash"
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
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  onUpdate: T.func.isRequired,
  onDelete: T.func.isRequired
}

const OddList = (props) =>
  <FormGroup
    id="item-odds"
    label={trans('odds', {}, 'quiz')}
    optional={true}
  >
    {0 === props.items.filter(item => utils.isOdd(item.id, props.solutions)).length &&
      <div className="no-item-info">{trans('no_odd_info', {}, 'quiz')}</div>
    }

    {0 < props.items.filter(item => utils.isOdd(item.id, props.solutions)).length &&
      <ul>
        {props.items.filter(item => utils.isOdd(item.id, props.solutions)).map((oddItem, oddIndex) =>
          <li key={oddItem.id}>
            <Odd
              index={oddIndex}
              odd={oddItem}
              solution={props.solutions.odd.find(o => o.itemId === oddItem.id)}
              onUpdate={(property, value) => updateItem(property, value, oddItem.id, props.items, props.solutions, true, props.onChange)}
              onDelete={() => props.delete(oddItem.id)}
              hasScore={props.hasScore}
              hasExpectedAnswers={props.hasExpectedAnswers}
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
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  onChange: T.func.isRequired,

  add: T.func.isRequired,
  delete: T.func.isRequired
}

const SetEditor = (props) => {
  const Set = (
    <div className="row" key="set-editor">
      <div key="items" className="items-col col-md-5 col-sm-5 col-xs-5">
        <ItemList
          key="items"
          items={props.item.items}
          solutions={props.item.solutions}
          onChange={props.update}

          add={() => addItem(props.item.items, props.update)}
          delete={(itemId) => removeItem(itemId, props.item.items, props.item.solutions, props.update)}
        />

        <OddList
          key="odd"
          items={props.item.items}
          solutions={props.item.solutions}
          onChange={props.update}
          hasScore={props.hasAnswerScores}
          hasExpectedAnswers={props.item.hasExpectedAnswers}

          add={() => addOdd(props.item.items, props.item.solutions, props.update)}
          delete={(itemId) => removeItem(itemId, props.item.items, props.item.solutions, props.update)}
        />
      </div>

      <div key="sets" className="sets-col col-md-7 col-sm-7 col-xs-7">
        <SetList
          sets={props.item.sets}
          associations={props.item.solutions.associations.map(association => {
            const itemIndex = props.item.items.findIndex(item => association.itemId === item.id)

            return Object.assign({
              _itemIndex: itemIndex,
              _itemData: props.item.items[itemIndex].data
            }, association)
          })}
          onChange={props.update}
          hasScore={props.hasAnswerScores}
          hasExpectedAnswers={props.item.hasExpectedAnswers}
        />
      </div>
    </div>
  )

  return (
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
              component: Set
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
              displayed: (item) => item.hasExpectedAnswers && props.hasAnswerScores && item.score.type === SCORE_SUM,
              options: {
                min: 0
              }
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(SetEditor, ItemEditorType, {
  item: T.shape(SetItemType.propTypes).isRequired
})

export {
  SetEditor
}
