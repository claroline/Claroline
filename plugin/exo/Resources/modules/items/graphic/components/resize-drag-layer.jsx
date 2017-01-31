import React, {Component, PropTypes as T} from 'react'
import {DragLayer} from 'react-dnd'
import {TYPE_AREA_RESIZER} from './../enums'
import {resizeArea} from './../resize'
import {AnswerArea} from './answer-area.jsx'

// this class doesn't hold any state but the drag layer decorator
// requires it to be a "full" component, not a stateless function
class ResizeDragLayer extends Component {
  render() {
    if (
      !this.props.canDrag ||
      !this.props.isDragging ||
      !this.props.currentOffset ||
      this.props.itemType !== TYPE_AREA_RESIZER
    ) {
      return null
    }

    const area = this.props.areas.find(area => area.id === this.props.item.areaId)

    return (
      <AnswerArea
        id="area-drag-preview"
        color={area.color}
        shape={area.shape}
        geometry={resizeArea(
          area,
          this.props.item.position,
          this.props.currentOffset.x,
          this.props.currentOffset.y
        )}
        selected={true}
        resizable={false}
        canDrag={false}
        isDragging={false}
        onSelect={() => {}}
        onDelete={() => {}}
        togglePopover={() => {}}
        connectDragSource={el => el}
      />
    )
  }
}

ResizeDragLayer.propTypes = {
  item: T.object,
  itemType: T.string,
  canDrag: T.bool.isRequired,
  isDragging: T.bool.isRequired,
  currentOffset: T.shape({
    x: T.number.isRequired,
    y: T.number.isRequired
  }),
  areas: T.arrayOf(T.shape({
    id: T.string.isRequired,
    shape: T.string.isRequired,
    color: T.string.isRequired
  })).isRequired
}

function collect(monitor) {
  return {
    item: monitor.getItem(),
    itemType: monitor.getItemType(),
    isDragging: monitor.isDragging(),
    currentOffset: monitor.getDifferenceFromInitialOffset()
  }
}

const ResizeDragLayerDecorated = DragLayer(collect)(ResizeDragLayer)

export {ResizeDragLayerDecorated as ResizeDragLayer}
