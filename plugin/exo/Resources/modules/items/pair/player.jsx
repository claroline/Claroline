import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import times from 'lodash/times'
import {tex, t} from '#/main/core/translation'
import {utils} from './utils/utils'
import {makeDraggable, makeDroppable} from './../../utils/dragAndDrop'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {PairItemDragPreview} from './pair-item-drag-preview.jsx'

let DropBox = props => {
  return props.connectDropTarget (
     <div className={classes(
       'pair-item-placeholder drop-placeholder placeholder-hover',
       {'hover': props.isOver}
     )}>
       <span className="fa fa-fw fa-share fa-rotate-90" />
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

const PairItem = props =>
  <div className="pair-item">
    {props.item.removable &&
      <TooltipButton
        id={`pair-${props.item.id}-delete`}
        className="btn-link-default btn-item-remove pull-right"
        title={t('delete')}
        onClick={() => props.handleItemRemove(props.item.id)}
      >
        <span className="fa fa-fw fa-trash-o" />
      </TooltipButton>
    }
    <div className="item-content" dangerouslySetInnerHTML={{__html: props.item.data}} />
  </div>

PairItem.propTypes = {
  item: T.object.isRequired,
  handleItemRemove: T.func.isRequired
}

const PairRow = props =>
  <div className="pair answer-item">
    {props.row[0] === -1 ?
      <DropBox object={{x: props.rowId, y: 0}} onDrop={props.onDrop}/> :
      <PairItem item={props.row[0]} handleItemRemove={props.onRemove}/>
    }
    {props.row[1] === -1 ?
      <DropBox object={{x: props.rowId, y: 1}} onDrop={props.onDrop} /> :
      <PairItem item={props.row[1]} handleItemRemove={props.onRemove}/>
    }
  </div>

PairRow.propTypes = {
  row: T.array.isRequired,
  rowId: T.number.isRequired,
  onDrop: T.func.isRequired,
  onRemove: T.func.isRequired
}

const PairRowList = props =>
  <ul>
    {times(props.rows, i =>
      <li key={i}>
        <PairRow key={i} rowId={i} row={props.answerItems[i]} onDrop={props.onItemDrop} onRemove={props.onItemRemove}/>
      </li>
    )}
  </ul>


PairRowList.propTypes = {
  answerItems: T.arrayOf(T.array).isRequired,
  rows: T.number.isRequired,
  onItemDrop: T.func.isRequired,
  onItemRemove: T.func.isRequired
}

let Item = props => {
  return (
    <div className="answer-item item">
      {props.connectDragSource(
        <div className="btn-drag pull-right">
          <OverlayTrigger
            placement="top"
            overlay={
              <Tooltip id={`item-${props.item.id}-drag`}>{t('move')}</Tooltip>
            }>
            <span
              draggable="true"
              className={classes(
                'btn',
                'btn-link-default',
                'drag-handle'
              )}
            >
              <span className="fa fa-fw fa-arrows" />
            </span>
          </OverlayTrigger>
        </div>
      )}
      <div className="item-content" dangerouslySetInnerHTML={{__html: props.item.data}} />
    </div>
  )
}

Item.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired
}

Item = makeDraggable(
  Item,
  'ITEM',
  PairItemDragPreview  
)

const ItemList = props =>
  <ul>
    {props.items.map((item) => {
      return item.display &&
        <li key={item.id}>
          <Item item={item}/>
        </li>
    })}
  </ul>


ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired
}

class PairPlayer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      items: utils.pairItemsWithDisplayOption(props.item.items),
      answerItems: utils.generateAnswerPairItems(props.item.items, props.item.rows)
    }
  }

  updateAnswer() {
    this.props.onChange(
      utils.generateAnswer(this.state.answerItems)
    )
  }

  handleItemRemove(itemId) {
    this.setState(
      {
        answerItems: utils.removeAnswerItem(this.state.answerItems, itemId),
        items: utils.switchItemDisplay(this.state.items, itemId, true)
      },
      () => {this.updateAnswer()}
    )
  }

  handleItemDrop(source, target) {
    this.setState(
      {
        answerItems: utils.addAnswerItem(this.state.answerItems, source.item, target.object.x, target.object.y),
        items: utils.switchItemDisplay(this.state.items, source.item.id, false)
      },
      () => {this.updateAnswer()}
    )
  }

  render() {
    return (
      <div className="pair-player row">
        <div className="col-md-5 col-sm-5 items-col">
            <ItemList items={this.state.items} />
        </div>

        <div className="col-md-7 col-sm-7 pairs-col">
            <PairRowList
              rows={this.props.item.rows}
              answerItems={this.state.answerItems}
              onItemDrop={(source, target) => this.handleItemDrop(source, target)}
              onItemRemove={(itemId) => this.handleItemRemove(itemId)}
            />
        </div>
      </div>
    )
  }
}

PairPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    items: T.arrayOf(T.object).isRequired,
    random: T.bool.isRequired,
    rows: T.number.isRequired
  }).isRequired,
  answer: T.arrayOf(T.array),
  onChange: T.func.isRequired
}

PairPlayer.defaultProps = {
  answer: []
}

export {PairPlayer}
