import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {t, tex} from '#/main/core/translation'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'
import {makeSortable, SORT_HORIZONTAL, SORT_VERTICAL} from './../../utils/sortable'
import {ErrorBlock} from '#/main/core/layout/form/components/error-block.jsx'
import {Textarea} from '#/main/core/layout/form/components/textarea.jsx'
import {CheckGroup} from './../../components/form/check-group.jsx'
import {Radios} from './../../components/form/radios.jsx'
import {FormGroup} from '#/main/core/layout/form/components/form-group.jsx'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import {MODE_INSIDE, MODE_BESIDE, DIRECTION_HORIZONTAL, DIRECTION_VERTICAL, actions} from './editor'
import {OrderingItemDragPreview} from './ordering-item-drag-preview.jsx'

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
            content={this.props.data}
            onChange={data => this.props.onChange(
              actions.updateItem(this.props.id, 'data', data)
            )}
          />
        {this.props.item.direction === DIRECTION_VERTICAL && this.state.showFeedback &&
            <div className="feedback-container">
              <Textarea
                id={`item-${this.props.id}-feedback`}
                title={tex('ordering_feedback')}
                content={this.props.feedback}
                onChange={text => this.props.onChange(
                  actions.updateItem(this.props.id, 'feedback', text)
                )}
              />
            </div>
          }
        </div>
        <div className="right-controls">
          {!this.props.fixedScore &&
            <input
              title={tex('score')}
              type="number"
              min={this.props.isOdd ? '' : 0}
              max={this.props.isOdd ? 0 : ''}
              className="form-control item-score"
              value={this.props.score}
              onChange={e => this.props.onChange(
                actions.updateItem(this.props.id, 'score', e.target.value)
              )}
            />
          }
          <TooltipButton
            id={`item-${this.props.id}-feedback-toggle`}
            className="btn-link-default"
            label={<span className="fa fa-fw fa-comments-o"></span>}
            title={tex('choice_feedback_info')}
            onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
          />
          <TooltipButton
            id={`item-${this.props.id}-delete`}
            className="btn-link-default"
            label={<span className="fa fa-fw fa-trash-o"></span>}
            enabled={this.props.deletable}
            title={t('delete')}
            onClick={() => this.props.onChange(
              actions.removeItem(this.props.id)
            )}
          />
        </div>
        {this.props.item.direction === DIRECTION_HORIZONTAL && this.state.showFeedback &&
          <div className="feedback-container">
            <Textarea
              id={`item-${this.props.id}-feedback`}
              title={tex('ordering_feedback')}
              content={this.props.feedback}
              onChange={text => this.props.onChange(
                actions.updateItem(this.props.id, 'feedback', text)
              )}
            />
          </div>
        }
      </div>
    )
  }
}

Item.propTypes = {
  item: T.object.isRequired,
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

let OrderingItem = props => {
  return props.connectDropTarget (
    <div className="item-container">
      <Item {...props} />
      {props.connectDragSource(
        <span
          title={t('move')}
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
}

OrderingItem.propTypes = {
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
  id: T.string.isRequired,
  data: T.string.isRequired,
  score: T.number.isRequired,
  feedback: T.string,
  fixedScore: T.bool.isRequired,
  onChange: T.func.isRequired
}

let ItemList = props => {
  const items = props.isOdd ? props.item.items.filter(el => undefined === el._position) : props.item.items.filter(el => undefined !== el._position)
  return  (
    <ul>
      { items.map((el, index) =>
        <li
          className={props.item.direction === DIRECTION_VERTICAL ? 'vertical':'horizontal'}
          key={el.id}>
          { props.isOdd ?
            <OrderingOdd
              id={el.id}
              data={el.data}
              score={el._score}
              feedback={el._feedback}
              deletable={true}
              fixedScore={props.item.score.type === SCORE_FIXED}
              {...props}
            />
            :
            <OrderingItem
              sortDirection={props.item.direction === DIRECTION_VERTICAL ? SORT_VERTICAL : SORT_HORIZONTAL}
              onSort={(a, b) => props.onChange(actions.moveItem(a, b))}
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
  )
}

ItemList.propTypes = {
  item: T.shape({
    direction: T.string.isRequired,
    mode: T.string.isRequired,
    score: T.shape({
      type: T.string.isRequired
    }),
    items: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired,
      _feedback: T.string,
      _deletable: T.bool.isRequired,
      _score: T.number.isRequired
    })).isRequired,
    _errors: T.object
  }).isRequired,
  isOdd: T.bool.isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const OrderingItems = props => {
  return (
    <div>
      {get(props.item, '_errors.items') &&
        <ErrorBlock text={props.item._errors.items} warnOnly={!props.validating}/>
      }
      <div className="items-row">
        <ItemList
          {...props}
          isOdd={false}/>
        <div className="item-footer">
          <button
            type="button"
            className="btn btn-default"
            onClick={() => props.onChange(actions.addItem(false))}
          >
            <span className="fa fa-plus"/>
            {tex('ordering_add_item')}
          </button>
        </div>
      </div>

      {props.item.mode === MODE_BESIDE &&
        <div className="odd-row">
          <ItemList
            {...props}
            isOdd={true}/>
          <div className="item-footer">
            <button
              type="button"
              className="btn btn-default"
              onClick={() => props.onChange(actions.addItem(true))}
            >
              <span className="fa fa-plus"/>
              {tex('ordering_add_odd')}
            </button>
          </div>
        </div>
      }
    </div>
  )
}

OrderingItems.propTypes = {
  item: T.shape({
    direction: T.string.isRequired,
    mode: T.string.isRequired,
    score: T.shape({
      type: T.string.isRequired
    }),
    items: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired,
      _feedback: T.string,
      _deletable: T.bool.isRequired,
      _score: T.number.isRequired
    })).isRequired,
    _errors: T.object
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

export const Ordering = props => {
  return (
    <fieldset className="ordering-editor">
      <CheckGroup
        checkId={`item-${props.item.id}-fixedScore`}
        checked={props.item.score.type === SCORE_FIXED}
        label={tex('fixed_score')}
        onChange={checked => props.onChange(
          actions.updateProperty('score.type', checked ? SCORE_FIXED : SCORE_SUM)
        )}
      />
      {props.item.score.type === SCORE_SUM &&
        <div className="form-group">
          <label htmlFor="ordering-penalty">{tex('ordering_editor_penalty_label')}</label>
          <input
            id="ordering-penalty"
            className="form-control"
            value={props.item.penalty}
            type="number"
            min="0"
            onChange={e => props.onChange(
               actions.updateProperty('penalty', e.target.value)
            )}
          />
        </div>
      }
      {props.item.score.type === SCORE_FIXED &&
        <div className="sub-fields">
          <FormGroup
            controlId={`item-${props.item.id}-fixedSuccess`}
            label={tex('fixed_score_on_success')}
            error={get(props.item, '_errors.score.success')}
            warnOnly={!props.validating}
          >
            <input
              id={`item-${props.item.id}-fixedSuccess`}
              type="number"
              min="0"
              value={props.item.score.success}
              className="form-control"
              onChange={e => props.onChange(
                actions.updateProperty('score.success', e.target.value)
              )}
            />
          </FormGroup>
          <FormGroup
            controlId={`item-${props.item.id}-fixedFailure`}
            label={tex('fixed_score_on_failure')}
            error={get(props.item, '_errors.score.failure')}
            warnOnly={!props.validating}
          >
            <input
              id={`item-${props.item.id}-fixedFailure`}
              type="number"
              value={props.item.score.failure}
              className="form-control"
              onChange={e => props.onChange(
                actions.updateProperty('score.failure', e.target.value)
              )}
            />
          </FormGroup>
        </div>
      }
      <Radios
        groupName="direction"
        options={[
          {value: DIRECTION_VERTICAL, label: tex('ordering_direction_vertical')},
          {value: DIRECTION_HORIZONTAL, label: tex('ordering_direction_horizontal')}
        ]}
        checkedValue={props.item.direction}
        inline={true}
        onChange={value => props.onChange(
          actions.updateProperty('direction', value)
        )}
      />
      <Radios
        groupName="mode"
        options={[
          {value: MODE_INSIDE, label: tex('ordering_mode_inside')},
          {value: MODE_BESIDE, label: tex('ordering_mode_beside')}
        ]}
        checkedValue={props.item.mode}
        inline={true}
        onChange={value => props.onChange(
          actions.updateProperty('mode', value)
        )}
      />
      <hr/>
      <OrderingItems {...props}/>

    </fieldset>
  )
}


Ordering.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    direction: T.string.isRequired,
    penalty: T.number.isRequired,
    mode: T.string.isRequired,
    score: T.shape({
      type: T.string.isRequired,
      success: T.number.isRequired,
      failure: T.number.isRequired
    }),
    items: T.arrayOf(T.object).isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}
