import {DragSource, DropTarget} from 'react-dnd'

export function makeDraggable(component, type, itemFactory = null) {
  const source = {
    beginDrag(props) {
      if (itemFactory) {
        return itemFactory(props)
      }

      return {
        item: props.item
      }
    },
    canDrag(props) {
      if (typeof props.canDrag !== 'undefined') {
        return props.canDrag
      }

      return true
    }
  }

  return DragSource(type, source, collectDrag)(component)
}

function collectDrag(connect, monitor) {
  return {
    connectDragSource: connect.dragSource(),
    connectDragPreview: connect.dragPreview(),
    isDragging: monitor.isDragging()
  }
}

export function makeDroppable(component, type) {
  const target = {
    drop(props, monitor) {
      const offset = monitor.getDifferenceFromInitialOffset()
      props.onDrop(monitor.getItem(), props, offset)
    }
  }
  return DropTarget(type, target, collectDrop)(component)
}

function collectDrop(connect, monitor) {
  return {
    connectDropTarget: connect.dropTarget(),
    isOver: monitor.isOver(),
    canDrop: monitor.canDrop()
  }
}
