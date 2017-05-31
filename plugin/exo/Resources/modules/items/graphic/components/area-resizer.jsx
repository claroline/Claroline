import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {makeDraggable} from './../../../utils/dragAndDrop'
import {TYPE_AREA_RESIZER, DIRECTIONS} from './../enums'

export const AreaResizer = props => {
  if (props.isDragging && props.areaEl) {
    props.areaEl.style.opacity = 0
    return null
  }

  if (props.areaEl) {
    props.areaEl.style.opacity = 1
  }

  return props.connectDragSource(
    <span
      className={classes('resizer', props.position)}
      style={{
        display: 'inline-block',
        position: 'absolute',
        top: props.top,
        left: props.left,
        width: props.size,
        height: props.size
      }}
    />
  )
}

AreaResizer.propTypes = {
  areaId: T.string.isRequired,
  top: T.number.isRequired,
  left: T.number.isRequired,
  size: T.number.isRequired,
  position: T.oneOf(DIRECTIONS).isRequired,
  isDragging: T.bool,
  connectDragSource: T.func.isRequired,
  areaEl: T.object
}

export const AreaResizerDraggable = makeDraggable(
  AreaResizer,
  TYPE_AREA_RESIZER,
  null,
  props => ({
    type: TYPE_AREA_RESIZER,
    areaId: props.areaId,
    position: props.position
  })
)
