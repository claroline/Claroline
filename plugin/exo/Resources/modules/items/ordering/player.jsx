import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'

import {t, tex} from '#/main/core/translation'
import {MODE_INSIDE, MODE_BESIDE, DIRECTION_HORIZONTAL, DIRECTION_VERTICAL} from './editor'
import {makeSortable, SORT_HORIZONTAL, SORT_VERTICAL} from './../../utils/sortable'
import {makeDraggable, makeDroppable} from './../../utils/dragAndDrop'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {OrderingItemDragPreview} from './ordering-item-drag-preview.jsx'

let DropBox = props => {
  return props.connectDropTarget (
     <div className={classes(
       'drop-container',
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
  canDrop: T.bool.isRequired
}

DropBox = makeDroppable(DropBox, 'ITEM')

let SortableItem = props => {
  return props.connectDropTarget (
    props.connectDragSource(
      <div className="item">
        <div className="item-data" dangerouslySetInnerHTML={{__html: props.data}} />
        <div className="item-actions">
          {props.canDelete &&
            <TooltipButton
              id={`answer-${props.index}-delete`}
              title={t('delete')}
              onClick={props.onDelete}
            >
              <span className="fa fa-fw fa-trash-o" />
            </TooltipButton>
          }
          <span
            title={t('move')}
            draggable="true"
            className="tooltiped-button btn"
          >
            <span className="fa fa-arrows drag-handle"/>
          </span>
        </div>
      </div>
    )
  )
}

SortableItem.propTypes = {
  data: T.string.isRequired,
  canDelete: T.bool.isRequired,
  onDelete: T.func,
  connectDragSource: T.func.isRequired,
  connectDropTarget: T.func.isRequired,
  onSort: T.func.isRequired,
  index: T.number.isRequired
}

SortableItem = makeSortable(
  SortableItem,
  'ORDERING_ITEM',
  OrderingItemDragPreview
)

let DraggableItem = props => {
  return props.connectDragSource(
    <div className="item">
      <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.data}} />      
      <span
        title={t('move')}
        draggable="true"
        className="tooltiped-button btn"
      >
        <span className="fa fa-arrows drag-handle"/>
      </span>
    </div>
  )
}

DraggableItem.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired
}

DraggableItem = makeDraggable(
  DraggableItem,
  'ITEM',
  OrderingItemDragPreview
)

class OrderingPlayer extends Component {

  constructor(props) {
    super(props)
  }

  componentDidMount() {
    if (this.props.item.mode === MODE_INSIDE && (this.props.answer.length === 0 || !this.props.answer)) {
      const answers = []
      this.props.item.items.forEach((item, index) => {
        answers.push({
          itemId: item.id,
          position: index + 1,
          _data: item.data
        })
      })
      this.props.onChange(answers)
    }
  }

  onItemDrop(source) {
    // add item to answers at the last position
    if (undefined === this.props.answer.find(a => a.itemId === source.item.id)) {
      const position = this.props.answer.length + 1
      // add to answer
      this.props.onChange(
        [{itemId: source.item.id, position:position, _data: source.item.data}].concat(this.props.answer).sort((a, b) => {
          return a.position - b.position
        })
      )
    }
  }

  onDelete(id) {
    const answers = cloneDeep(this.props.answer.filter(answer => answer.itemId !== id))
    answers.map((answer, index) => answer.position = index + 1)
    this.props.onChange(
       answers
    )
  }

  onSort(id, swapId) {
    const newAnswer = cloneDeep(this.props.answer)
    // previous index of the dragged item
    const answerIndex = this.props.answer.findIndex(a => a.itemId === id)
    // new index of the dragged item
    const swapIndex = this.props.answer.findIndex(a => a.itemId === swapId)

    const tempAnswer = cloneDeep(this.props.answer.find(a => a.itemId === id))
    const tempSwap = cloneDeep(this.props.answer.find(a => a.itemId === swapId))
    tempAnswer.position = swapIndex + 1
    tempSwap.position = answerIndex + 1
    newAnswer[swapIndex] = tempAnswer
    newAnswer[answerIndex] = tempSwap

    return newAnswer
  }

  render() {

    return (
      <div className="ordering-player">
        <div className="row">
          <div className={classes(
              {'horizontal': this.props.item.direction === DIRECTION_HORIZONTAL},
              {'col-md-12': this.props.item.mode === MODE_INSIDE},
              {'col-md-6': this.props.item.direction === DIRECTION_VERTICAL && this.props.item.mode === MODE_BESIDE}
            )}>
            {this.props.item.mode === MODE_INSIDE ?
              this.props.answer.map((a, index) =>
                <SortableItem
                  id={a.itemId}
                  key={a.itemId}
                  data={a._data}
                  canDelete={false}
                  index={index}
                  sortDirection={this.props.item.direction === DIRECTION_VERTICAL ? SORT_VERTICAL : SORT_HORIZONTAL}
                  onSort={(id, swapId) => this.props.onChange(
                    this.onSort(id, swapId)
                  )}/>
              )
              :
              this.props.item.items.filter(item => undefined === this.props.answer.find(answer => answer.itemId === item.id)).map((item) =>
                <DraggableItem
                  item={item}
                  key={item.id}/>
              )
            }
          </div>
          {this.props.item.direction === DIRECTION_VERTICAL && this.props.item.mode === MODE_BESIDE &&
            <div className="col-md-6 answer-zone">
              {this.props.answer.map((a, index) =>
                <SortableItem
                  id={a.itemId}
                  key={a.itemId}
                  data={a._data}
                  canDelete={true}
                  onDelete={() => this.onDelete(a.itemId)}
                  sortDirection={SORT_VERTICAL}
                  index={index}
                  onSort={(id, swapId) => this.props.onChange(
                    this.onSort(id, swapId)
                  )}/>
              )}
              <DropBox onDrop={(source) => this.onItemDrop(source)}/>
            </div>
          }
        </div>
        {this.props.item.direction === DIRECTION_HORIZONTAL && this.props.item.mode === MODE_BESIDE &&
          <div className="row">
            <div className="col-md-12 answer-zone horizontal">
              {this.props.answer.map((a, index) =>
                <SortableItem
                  id={a.itemId}
                  key={a.itemId}
                  data={a._data}
                  canDelete={true}
                  onDelete={() => this.onDelete(a.itemId)}
                  sortDirection={SORT_HORIZONTAL}
                  index={index}
                  onSort={(id, swapId) => this.props.onChange(
                    this.onSort(id, swapId)
                  )}/>
              )}
              <DropBox onDrop={(source) => this.onItemDrop(source)}/>
            </div>
          </div>
        }
      </div>
    )
  }

}

OrderingPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    direction: T.string.isRequired,
    mode: T.string.isRequired,
    items: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array.isRequired,
  onChange: T.func.isRequired
}

OrderingPlayer.defaultProps = {
  answer: []
}

export {OrderingPlayer}
