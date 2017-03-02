import React, {Component, PropTypes as T} from 'react'
import get from 'lodash/get'
import classes from 'classnames'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import {tex, t} from './../../utils/translate'
import {Textarea} from './../../components/form/textarea.jsx'
import {ErrorBlock} from './../../components/form/error-block.jsx'
import {makeDraggable, makeDroppable} from './../../utils/dragAndDrop'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import {actions} from './editor'

let DropBox = props => {
  return props.connectDropTarget (
     <div className={classes(
       'set-item-drop-container',
       {'on-hover': props.isOver}
     )}>
       {tex('set_drop_item')}
     </div>
   )
}

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
      <div className={classes('association', {'positive-score' : this.props.association.score > 0}, {'negative-score': this.props.association.score < 1})}>
        <div className="first-row">
          <div className="association-data" dangerouslySetInnerHTML={{__html: this.props.association._itemData}} />

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
            <TooltipButton
              id={`ass-${this.props.association.itemId}-${this.props.association.setId}-feedback-toggle`}
              className="fa fa-comments-o"
              title={tex('feedback_association_created')}
              onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
            />
            <TooltipButton
              id={`ass-${this.props.association.itemId}-${this.props.association.setId}-delete`}
              className="fa fa-trash-o"
              title={t('delete')}
              onClick={() => this.props.onChange(
                actions.removeAssociation(this.props.association.setId, this.props.association.itemId))
              }
            />
          </div>

        </div>
        {this.state.showFeedback &&
          <div className="feedback-container">
            <Textarea
              onChange={(value) => this.props.onChange(
                actions.updateAssociation(this.props.association.setId, this.props.association.itemId, 'feedback', value)
              )}
              id={`${this.props.association.itemId}-${this.props.association.setId}-feedback`}
              content={this.props.association.feedback}
            />
          </div>
        }
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
        <div className="set">
          <div className="set-heading">
            <div className="text-fields">
              <Textarea
                onChange={(value) => this.props.onChange(
                  actions.updateSet(this.props.set.id, 'data', value)
                )}
                id={`${this.props.set.id}-data`}
                content={this.props.set.data}
              />
            </div>
            <div className="right-controls">
              <TooltipButton
                id={`set-${this.props.set.id}-delete`}
                className="fa fa-trash-o"
                title={t('delete')}
                enabled={this.props.set._deletable}
                onClick={() => this.props.onChange(
                  actions.removeSet(this.props.set.id))
                }
              />
            </div>
          </div>
          <div className="set-body">
            <ul>
            { this.props.associations.map(ass =>
              <li key={`${ass.itemId}-${ass.setId}`}>
                <Association association={ass} onChange={this.props.onChange}/>
              </li>
            )}
            </ul>
            <DropBox object={this.props.set} onDrop={this.props.onDrop} />
          </div>
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
    if(undefined === this.props.solutions.associations.find(el => el.setId === target.object.id && el.itemId === source.item.id)){
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
        <div className="footer text-center">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => this.props.onChange(actions.addSet())}
          >
            <span className="fa fa-plus"/>
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
  return props.connectDragPreview (
    <div className="item">
      <div className="text-fields">
        <Textarea
          onChange={(value) => props.onChange(
            actions.updateItem(props.item.id, 'data', value, false)
          )}
          id={`${props.item.id}-data`}
          content={props.item.data}
        />
      </div>
      <div className="right-controls">
        <TooltipButton
          id={`set-item-${props.item.id}-delete`}
          className="fa fa-trash-o"
          title={t('delete')}
          enabled={props.item._deletable}
          onClick={() => props.onChange(
             actions.removeItem(props.item.id, false)
          )}
        />
        {props.connectDragSource(
          <div>
            <OverlayTrigger
              placement="top"
              overlay={
                <Tooltip id={`set-item-${props.item.id}-drag`}>{t('move')}</Tooltip>
              }>
              <span
                title={t('move')}
                draggable="true"
                className={classes(
                  'tooltiped-button',
                  'btn',
                  'fa',
                  'fa-bars',
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
  connectDragPreview: T.func.isRequired,
  item: T.object.isRequired
}

Item = makeDraggable(Item, 'ITEM')

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
            <span className="fa fa-plus"/>
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
      <div className={classes('item', {'positive-score' : this.props.solution.score > 0}, {'negative-score': this.props.solution.score < 1})}>
        <div className="text-fields">
          <Textarea
            onChange={(value) => this.props.onChange(
              actions.updateItem(this.props.odd.id, 'data', value, true)
            )}
            id={`odd-${this.props.odd.id}-data`}
            content={this.props.odd.data}
          />
          {this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                onChange={ (value) => this.props.onChange(
                  actions.updateItem(this.props.odd.id, 'feedback', value, true)
                )}
                id={`odd-${this.props.odd.id}-feedback`}
                content={this.props.solution.feedback}
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
          <TooltipButton
            id={`odd-${this.props.odd.id}-feedback-toggle`}
            className="fa fa-comments-o"
            title={tex('feedback')}
            onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
          />
          <TooltipButton
            id={`odd-${this.props.odd.id}-delete`}
            className="fa fa-trash-o"
            title={t('delete')}
            onClick={() => this.props.onChange(actions.removeItem(this.props.odd.id, true))}
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
        <div className="footer text-center">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => this.props.onChange(actions.addItem(true))}
          >
            <span className="fa fa-plus"/>
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
      <div className="set-question-editor">
        {get(this.props.item, '_errors.item') &&
          <ErrorBlock text={this.props.item._errors.item} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.items') &&
          <ErrorBlock text={this.props.item._errors.items} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.sets') &&
          <ErrorBlock text={this.props.item._errors.sets} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.solutions') &&
          <ErrorBlock text={this.props.item._errors.solutions} warnOnly={!this.props.validating}/>
        }
        {get(this.props.item, '_errors.odd') &&
          <ErrorBlock text={this.props.item._errors.odd} warnOnly={!this.props.validating}/>
        }
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
        <hr/>
        <div className="sets-builder-container">
          <div className="pool-col">
            <ItemList onChange={this.props.onChange} solutions={this.props.item.solutions} items={this.props.item.items} />
            <hr/>
            <OddList onChange={this.props.onChange} solutions={this.props.item.solutions} odd={this.props.item.items} />
          </div>
          <div className="sets-col">
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
