import React, {Component, PropTypes as T} from 'react'
import classes from 'classnames'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import {tex, t} from './../../utils/translate'
import {utils} from './utils/utils'
import {makeDraggable, makeDroppable} from './../../utils/dragAndDrop'
import shuffle from 'lodash/shuffle'
import times from 'lodash/times'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'

let DropBox = props => {
  return props.connectDropTarget (
     <div className={classes(
       'pair-item-drop-container',
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

const Pair = props =>
  <div className="pair-element">
    <div className="pair-element-data" dangerouslySetInnerHTML={{__html: props.item.data}} />
    {props.item.removable &&
      <div className="right-controls">
        <TooltipButton
          id={`pair-${props.item.id}-delete`}
          className="fa fa-trash-o"
          title={t('delete')}
          onClick={() => props.handleItemRemove(props.item.id)}
        />
      </div>
    }
  </div>

Pair.propTypes = {
  item: T.object.isRequired,
  handleItemRemove: T.func.isRequired
}

const PairRow = props =>
  <div className="pair-row">
    {props.row[0] === -1 ?
      <DropBox object={{x: props.rowId, y: 0}} onDrop={props.onDrop}/> :
      <Pair item={props.row[0]} handleItemRemove={props.onRemove}/>
    }
    {props.row[1] === -1 ?
      <DropBox object={{x: props.rowId, y: 1}} onDrop={props.onDrop} /> :
      <Pair item={props.row[1]} handleItemRemove={props.onRemove}/>
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
  return props.connectDragPreview (
    <div className="item">
      <div className="item-content" dangerouslySetInnerHTML={{__html: props.item.data}} />
      <div className="right-controls">
        {props.connectDragSource(
          <div>
            <OverlayTrigger
              placement="top"
              overlay={
                <Tooltip id={`item-${props.item.id}-drag`}>{t('move')}</Tooltip>
              }>
              <span
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
  connectDragSource: T.func.isRequired,
  connectDragPreview: T.func.isRequired,
  item: T.object.isRequired
}

Item = makeDraggable(Item, 'ITEM')

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
      items: this.randomize(utils.pairItemsWithDisplayOption(props.item.items), props.item.random),
      answerItems: utils.generateAnswerPairItems(props.item.items, props.item.rows)
    }
  }

  randomize(items, random) {
    return random ? shuffle(items) : items
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
      <div className="pair-question-player">
        <div className="items-col">
            <ItemList items={this.state.items} />
        </div>
        <div className="pair-rows-col">
            <PairRowList rows={this.props.item.rows}
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
