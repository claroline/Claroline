import React, {Component} from 'react'
import get from 'lodash/get'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

import {tex, trans} from '#/main/app/intl/translation'
import {Textarea} from '#/main/core/layout/form/components/field/textarea'
import {ContentError} from '#/main/app/content/components/error'
import {makeDraggable, makeDroppable} from './../../utils/dragAndDrop'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {actions} from './editor'
import {SetItemDragPreview} from './set-item-drag-preview.jsx'

let DropBox = props => props.connectDropTarget(
  <div className={classes('set-drop-placeholder', {
    hover: props.isOver
  })}>
    {tex('set_drop_item')}
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
                onChange={(value) => this.props.onChange(
                  actions.updateAssociation(this.props.association.setId, this.props.association.itemId, 'feedback', value)
                )}
              />
            </div>
          }
        </div>

        <div className="right-controls">
          <input
            title={tex('score')}
            type="number"
            className="form-control association-score"
            value={this.props.association.score}
            onChange={e => this.props.onChange(
              actions.updateAssociation(this.props.association.setId, this.props.association.itemId, 'score', e.target.value)
            )}
          />

          <Button
            id={`ass-${this.props.association.itemId}-${this.props.association.setId}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={tex('feedback_association_created')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`ass-${this.props.association.itemId}-${this.props.association.setId}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            label={trans('delete', {}, 'actions')}
            callback={() => this.props.onChange(
              actions.removeAssociation(this.props.association.setId, this.props.association.itemId))
            }
            tooltip="top"
          />
        </div>
      </div>
    )
  }
}

Association.propTypes = {
  onChange: T.func.isRequired,
  association: T.object.isRequired
}

class Set extends Component {

  constructor(props) {
    super(props)
  }

  render(){
    return (
      <div className="set answer-item">
        <div className="set-heading">
          <div className="text-fields">
            <Textarea
              id={`${this.props.set.id}-data`}
              value={this.props.set.data}
              onChange={(value) => this.props.onChange(
                actions.updateSet(this.props.set.id, 'data', value)
              )}
            />
          </div>

          <div className="right-controls">
            <Button
              id={`set-${this.props.set.id}-delete`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash-o"
              label={trans('delete', {}, 'actions')}
              disabled={!this.props.set._deletable}
              callback={() => this.props.onChange(
                actions.removeSet(this.props.set.id))
              }
              tooltip="top"
            />
          </div>
        </div>

        <ul>
          {this.props.associations.map(ass =>
            <li key={`${ass.itemId}-${ass.setId}`}>
              <Association association={ass} onChange={this.props.onChange}/>
            </li>
          )}
        </ul>

        <DropBox object={this.props.set} onDrop={this.props.onDrop} />
      </div>
    )
  }
}

Set.propTypes = {
  onChange: T.func.isRequired,
  set: T.object.isRequired,
  onDrop: T.func.isRequired,
  associations: T.arrayOf(T.object).isRequired
}

class SetList extends Component {

  constructor(props) {
    super(props)
  }

  /**
   * handle item drop
   * @var {source} dropped item (item)
   * @var {target} target item (set)
   */
  onItemDrop(source, target){
    // add solution (check the item is not already inside before adding it)
    if (undefined === this.props.solutions.associations.find(el => el.setId === target.object.id && el.itemId === source.item.id)){
      this.props.onChange(actions.addAssociation(target.object.id, source.item.id, source.item.data))
    }
  }

  render(){
    return (
      <div className="sets">
        <ul>
          {this.props.sets.map((set) =>
            <li key={`set-id-${set.id}`}>
              <Set
                associations={
                  this.props.solutions.associations.filter(association => association.setId === set.id) || []
                }
                onDrop={
                  (source, target) => this.onItemDrop(source, target)
                }
                onChange={this.props.onChange}
                set={set}
              />
            </li>
          )}
        </ul>
        <div className="footer">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => this.props.onChange(actions.addSet())}
          >
            <span className="fa fa-fw fa-plus"/>
            {tex('set_add_set')}
          </button>
        </div>
      </div>
    )
  }
}

SetList.propTypes = {
  onChange: T.func.isRequired,
  sets: T.arrayOf(T.object).isRequired,
  solutions: T.shape({
    associations: T.arrayOf(T.object).isRequired,
    odds: T.arrayOf(T.object)
  }).isRequired
}

let Item = props => {
  return (
    <div className="set-item answer-item">
      <div className="text-fields">
        <Textarea
          id={`${props.item.id}-data`}
          value={props.item.data}
          onChange={(value) => props.onChange(
            actions.updateItem(props.item.id, 'data', value, false)
          )}
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
          callback={() => props.onChange(
            actions.removeItem(props.item.id, false)
          )}
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
  )
}

Item.propTypes = {
  onChange: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired
}

Item = makeDraggable(
  Item,
  'ITEM',
  SetItemDragPreview
)

class ItemList extends Component {
  constructor(props) {
    super(props)
  }

  render(){
    return (
      <div className="item-list">
        <ul>
          { this.props.items.filter(item => undefined === this.props.solutions.odd.find(el => el.itemId === item.id)).map((item) =>
            <li key={item.id}>
              <Item onChange={this.props.onChange} item={item}/>
            </li>
          )}
        </ul>
        <div className="footer text-center">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => this.props.onChange(actions.addItem(false))}
          >
            <span className="fa fa-fw fa-plus"/>
            {tex('set_add_item')}
          </button>
        </div>
      </div>
    )
  }
}

ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired,
  onChange: T.func.isRequired,
  solutions: T.object.isRequired
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
            onChange={(value) => this.props.onChange(
              actions.updateItem(this.props.odd.id, 'data', value, true)
            )}
          />
          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`odd-${this.props.odd.id}-feedback`}
                value={this.props.solution.feedback}
                onChange={ (value) => this.props.onChange(
                  actions.updateItem(this.props.odd.id, 'feedback', value, true)
                )}
              />
            </div>
          }
        </div>
        <div className="right-controls">
          <input
            title={tex('score')}
            type="number"
            max="0"
            className="form-control odd-score"
            value={this.props.solution.score}
            onChange={e => this.props.onChange(
              actions.updateItem(this.props.odd.id, 'score', e.target.value, true)
            )}
          />
          <Button
            id={`odd-${this.props.odd.id}-feedback-toggle`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-comments-o"
            label={tex('feedback')}
            callback={() => this.setState({showFeedback: !this.state.showFeedback})}
            tooltip="top"
          />

          <Button
            id={`odd-${this.props.odd.id}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash-o"
            label={trans('delete', {}, 'actions')}
            onClick={() => this.props.onChange(actions.removeItem(this.props.odd.id, true))}
            tooltip="top"
          />
        </div>
      </div>
    )
  }
}

Odd.propTypes = {
  onChange: T.func.isRequired,
  odd: T.object.isRequired,
  solution: T.object.isRequired
}

class OddList extends Component {
  constructor(props){
    super(props)
  }

  render(){
    return (
      <div className="odd-list">
        <ul>
          { this.props.odd.filter(item => undefined !== this.props.solutions.odd.find(el => el.itemId === item.id)).map((oddItem) =>
            <li key={oddItem.id}>
              <Odd onChange={this.props.onChange} odd={oddItem} solution={this.props.solutions.odd.find(el => el.itemId === oddItem.id)}/>
            </li>
          )}
        </ul>

        <div className="footer">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => this.props.onChange(actions.addItem(true))}
          >
            <span className="fa fa-fw fa-plus"/>
            {tex('set_add_odd')}
          </button>
        </div>
      </div>
    )
  }
}

OddList.propTypes = {
  onChange: T.func.isRequired,
  odd: T.arrayOf(T.object).isRequired,
  solutions: T.object.isRequired
}

class SetForm extends Component {

  constructor(props) {
    super(props)
  }

  render() {
    return (
      <div className="set-editor">
        <div className="form-group">
          <label htmlFor="set-penalty">{tex('editor_penalty_label')}</label>
          <input
            id="set-penalty"
            className="form-control"
            value={this.props.item.penalty}
            type="number"
            min="0"
            onChange={e => this.props.onChange(
              actions.updateProperty('penalty', e.target.value)
            )}
          />
        </div>

        <hr/>

        <div className="checkbox">
          <label>
            <input
              type="checkbox"
              checked={this.props.item.random}
              onChange={e => this.props.onChange(
                actions.updateProperty('random', e.target.checked)
              )}
            />
            {tex('set_shuffle_labels_and_proposals')}
          </label>
        </div>

        {get(this.props.item, '_errors.item') &&
          <ContentError error={this.props.item._errors.item} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.items') &&
          <ContentError error={this.props.item._errors.items} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.sets') &&
          <ContentError error={this.props.item._errors.sets} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.solutions') &&
          <ContentError error={this.props.item._errors.solutions} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.odd') &&
          <ContentError error={this.props.item._errors.odd} warnOnly={!this.props.validating}/>
        }

        <div className="set-items row">
          <div className="col-md-5 col-sm-5 col-xs-5">
            <ItemList onChange={this.props.onChange} solutions={this.props.item.solutions} items={this.props.item.items} />

            <hr className="item-content-separator" />

            <OddList onChange={this.props.onChange} solutions={this.props.item.solutions} odd={this.props.item.items} />
          </div>

          <div className="col-md-7 col-sm-7 col-xs-7">
            <SetList solutions={this.props.item.solutions} onChange={this.props.onChange} sets={this.props.item.sets} />
          </div>
        </div>
      </div>
    )
  }
}

SetForm.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    random: T.bool.isRequired,
    penalty: T.number.isRequired,
    sets: T.arrayOf(T.object).isRequired,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.shape({
      associations: T.arrayOf(T.object).isRequired,
      odd: T.arrayOf(T.object).isRequired
    }).isRequired,
    _errors: T.object
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

export {SetForm}
