import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {DragLayer} from 'react-dnd'

import {TYPE_AREA_RESIZER} from './../items/graphic/enums'

function getItemStyles(props) {
  const { currentOffset } = props
  if (!currentOffset) {
    return {
      display: 'none'
    }
  }

  const {x, y} = currentOffset
  const transform = `translate(${x}px, ${y}px)`
  return {
    transform: transform
  }
}

class CustomDragLayerComponent extends Component {
  render() {
    const { item, isDragging } = this.props
    if (!isDragging || this.props.itemType === TYPE_AREA_RESIZER) {
      return null
    }

    return (
      <div className="custom-drag-layer">
        <div style={getItemStyles(this.props)}>
          {item.previewComponnent(item.props)}
        </div>
      </div>
    )
  }
}

CustomDragLayerComponent.propTypes = {
  item: T.object,
  itemType: T.string,
  currentOffset: T.shape({
    x: T.number.isRequired,
    y: T.number.isRequired
  }),
  isDragging: T.bool.isRequired
}

function collect(monitor) {
  return {
    item: monitor.getItem(),
    itemType: monitor.getItemType(),
    currentOffset: monitor.getSourceClientOffset(),
    isDragging: monitor.isDragging()
  }
}

const CustomDragLayer = DragLayer(collect)(CustomDragLayerComponent)

export {CustomDragLayer}
