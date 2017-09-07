import {DragSource, DropTarget} from 'react-dnd'
import {DefaultPreviewComponent} from './default-preview-component.jsx'
import {TYPE_AREA_RESIZER} from './../items/graphic/enums'

export function makeDraggable(component, type, previewComponent = null, itemFactory = null) {
  const source = {
    beginDrag(props) {
      if (type === TYPE_AREA_RESIZER) {
        return {
          id: props.areaId,
          item: itemFactory(props)
        }
      }

      return {
        id: props.id,
        item: itemFactory ? itemFactory(props) : props.item,
        props: props,
        previewComponent: previewComponent ? previewComponent : DefaultPreviewComponent
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
