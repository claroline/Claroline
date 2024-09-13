import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import times from 'lodash/times'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {ContentHtml} from '#/main/app/content/components/html'

import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {utils} from '#/plugin/exo/items/pair/utils'
import {PairItemDragPreview} from '#/plugin/exo/items/pair/components/pair-item-drag-preview'


let DropBox = props => props.connectDropTarget(
  <div className={classes('pair-item-placeholder drop-placeholder placeholder-md placeholder-hover', {
    hover: props.isOver
  })}>
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

const PairItem = props =>
  <div className="pair-item pair-answer-item">
    <ContentHtml className="pair-item-content">{props.item.data}</ContentHtml>
    {props.item.removable && props.removable &&
      <div className="item-actions" role="presentation">
        <Button
          id={`pair-${props.item.id}-delete`}
          type={CALLBACK_BUTTON}
          className="btn btn-text-secondary p-0"
          icon="fa fa-fw fa-trash"
          label={trans('delete', {}, 'actions')}
          callback={() => props.handleItemRemove(props.item.id)}
          tooltip="top"
        />
      </div>
    }
  </div>

PairItem.propTypes = {
  item: T.object.isRequired,
  removable: T.bool.isRequired,
  handleItemRemove: T.func.isRequired
}

const PairRow = props =>
  <div className="pair answer-item">
    {props.row[0] === -1 ?
      <DropBox object={{x: props.rowId, y: 0}} onDrop={props.onDrop}/> :
      <PairItem item={props.row[0]} removable={props.removable} handleItemRemove={props.onRemove}/>
    }
    {props.row[1] === -1 ?
      <DropBox object={{x: props.rowId, y: 1}} onDrop={props.onDrop} /> :
      <PairItem item={props.row[1]} removable={props.removable} handleItemRemove={props.onRemove}/>
    }
  </div>

PairRow.propTypes = {
  row: T.array.isRequired,
  rowId: T.number.isRequired,
  removable: T.bool.isRequired,
  onDrop: T.func.isRequired,
  onRemove: T.func.isRequired
}

const PairRowList = props =>
  <ul>
    {times(props.rows, i =>
      <li key={i}>
        <PairRow
          key={i}
          rowId={i}
          row={props.answerItems[i]}
          removable={props.removable}
          onDrop={props.onItemDrop}
          onRemove={props.onItemRemove}
        />
      </li>
    )}
  </ul>


PairRowList.propTypes = {
  answerItems: T.arrayOf(T.array).isRequired,
  rows: T.number.isRequired,
  removable: T.bool.isRequired,
  onItemDrop: T.func.isRequired,
  onItemRemove: T.func.isRequired
}

let Item = props => {
  const element =
    <div className={classes('pair-answer-item answer-item pair-item', {'drag-handle': props.draggable})}>
      <ContentHtml className="pair-item-content">{props.item.data}</ContentHtml>

      {props.draggable &&
        <div className="item-actions" role="presentation">
          <span className="drag-handle fa fa-arrows text-secondary" aria-hidden={true} />
        </div>
      }
    </div>

  return props.draggable ? props.connectDragSource(element) : element
}

Item.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired,
  draggable: T.bool.isRequired
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
          <Item item={item} draggable={props.draggable}/>
        </li>
    })}
  </ul>


ItemList.propTypes = {
  items:  T.arrayOf(T.object).isRequired,
  draggable: T.bool.isRequired
}

class PairPlayer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      items: utils.pairItemsWithDisplayOption(props.item.items).map(i => {
        let unused = true
        props.answer.forEach(a => {
          if (-1 < a.indexOf(i.id)) {
            unused = false
          }
        })

        return unused ? i : Object.assign({}, i, {display: false})
      }),
      answerItems: utils.generateAnswerPairItems(props.item.items, props.item.rows, props.answer)
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
      <div className="pair-player row user-select-none">
        <div className="col-md-5 col-sm-5 items-col">
          <ItemList items={this.state.items} draggable={!this.props.disabled}/>
        </div>

        <div className="col-md-7 col-sm-7 pairs-col">
          <PairRowList
            rows={this.props.item.rows}
            answerItems={this.state.answerItems}
            removable={!this.props.disabled}
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
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

PairPlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {PairPlayer}
