import {DragDropContext} from 'react-dnd'
import {default as TouchBackend} from 'react-dnd-touch-backend'

const DragNDropContext = (Component) => DragDropContext(
  TouchBackend({ enableMouseEvents: true })
)(Component)

export {
  DragNDropContext
}
