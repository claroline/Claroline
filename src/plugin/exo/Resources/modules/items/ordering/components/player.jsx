import React, {Component, forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import shuffle from 'lodash/shuffle'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {constants} from '#/plugin/exo/items/ordering/constants'
import {makeSortable, SORT_HORIZONTAL, SORT_VERTICAL} from '#/plugin/exo/utils/sortable'
import {makeDraggable, makeDroppable} from '#/plugin/exo/utils/dragAndDrop'
import {OrderingItemDragPreview} from '#/plugin/exo/items/ordering/components/ordering-item-drag-preview'

let DropBox = props => props.connectDropTarget(
  <div className={classes('drop-container', {
    'on-hover': props.isOver
  })}>
    {trans('set_drop_item', {}, 'quiz')}
  </div>
)

DropBox.propTypes = {
  connectDropTarget: T.func.isRequired,
  isOver: T.bool.isRequired,
  onDrop: T.func.isRequired,
  canDrop: T.bool.isRequired
}

DropBox = makeDroppable(DropBox, 'ITEM')

let SortableItem = forwardRef((props, ref) => {
  const element =
    <div className="item answer-item" ref={ref}>
      <div className="item-data" dangerouslySetInnerHTML={{__html: props.data}} />
      <div className="item-actions">
        {props.canDelete &&
          <Button
            id={`answer-${props.index}-delete`}
            className="btn-link"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-trash"
            label={trans('delete', {}, 'actions')}
            callback={props.onDelete}
            tooltip="top"
            dangerous={true}
          />
        }

        {props.sortable &&
          <span className="fa fa-arrows" />
        }
      </div>
    </div>

  return props.sortable ? props.connectDropTarget (props.connectDragSource(element)) : element
})

SortableItem.displayName = 'SortableItem'

SortableItem.propTypes = {
  data: T.string.isRequired,
  sortable: T.bool.isRequired,
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
  const element =
    <div className="item answer-item">
      <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.data}} />

      {props.draggable &&
      <span className="fa fa-arrows" />
      }
    </div>

  return props.draggable ? props.connectDragSource(element) : element
}

DraggableItem.propTypes = {
  connectDragSource: T.func.isRequired,
  item: T.object.isRequired,
  draggable: T.bool.isRequired
}

DraggableItem = makeDraggable(
  DraggableItem,
  'ITEM',
  OrderingItemDragPreview
)

class OrderingPlayer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      items: shuffle(this.props.item.items)
    }
  }

  componentDidMount() {
    if (this.props.item.mode === constants.MODE_INSIDE && (this.props.answer.length === 0 || !this.props.answer)) {
      const answers = []
      this.state.items.forEach((item, index) => {
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

    this.props.onChange(answers)
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
            {'horizontal': this.props.item.direction === constants.DIRECTION_HORIZONTAL},
            {'col-md-12': this.props.item.mode === constants.MODE_INSIDE},
            {'col-md-6': this.props.item.direction === constants.DIRECTION_VERTICAL && this.props.item.mode === constants.MODE_BESIDE}
          )}>
            {this.props.item.mode === constants.MODE_INSIDE ?
              this.props.answer.map((a, index) =>
                <SortableItem
                  id={a.itemId}
                  key={a.itemId}
                  data={a._data}
                  sortable={!this.props.disabled}
                  canDelete={false}
                  index={index}
                  sortDirection={this.props.item.direction === constants.DIRECTION_VERTICAL ? SORT_VERTICAL : SORT_HORIZONTAL}
                  onSort={(id, swapId) => this.props.onChange(
                    this.onSort(id, swapId)
                  )}/>
              )
              :
              this.state.items.filter(item => undefined === this.props.answer.find(answer => answer.itemId === item.id)).map((item) =>
                <DraggableItem
                  item={item}
                  key={item.id}
                  draggable={!this.props.disabled}
                />
              )
            }
          </div>
          {this.props.item.direction === constants.DIRECTION_VERTICAL && this.props.item.mode === constants.MODE_BESIDE &&
            <div className="col-md-6 answer-zone">
              {this.props.answer.map((a, index) =>
                <SortableItem
                  id={a.itemId}
                  key={a.itemId}
                  data={a._data}
                  sortable={!this.props.disabled}
                  canDelete={!this.props.disabled}
                  onDelete={() => this.onDelete(a.itemId)}
                  sortDirection={SORT_VERTICAL}
                  index={index}
                  onSort={(id, swapId) => this.props.onChange(
                    this.onSort(id, swapId)
                  )}/>
              )}
              {!this.props.disabled &&
                <DropBox onDrop={(source) => this.onItemDrop(source)}/>
              }
            </div>
          }
        </div>
        {this.props.item.direction === constants.DIRECTION_HORIZONTAL && this.props.item.mode === constants.MODE_BESIDE &&
          <div className="row">
            <div className="col-md-12 answer-zone horizontal">
              {this.props.answer.map((a, index) =>
                <SortableItem
                  id={a.itemId}
                  key={a.itemId}
                  data={a._data}
                  sortable={!this.props.disabled}
                  canDelete={!this.props.disabled}
                  onDelete={() => this.onDelete(a.itemId)}
                  sortDirection={SORT_HORIZONTAL}
                  index={index}
                  onSort={(id, swapId) => this.props.onChange(
                    this.onSort(id, swapId)
                  )}/>
              )}
              {!this.props.disabled &&
                <DropBox onDrop={(source) => this.onItemDrop(source)}/>
              }
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
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

OrderingPlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {
  OrderingPlayer
}
