import React from 'react'
import {PropTypes as T} from 'prop-types'

import {
  AREA_GUTTER
} from './answer-area.jsx'

import {
  SHAPE_RECT
} from './../enums'


export const AnswerAreaDragPreview = props => {

  const isRect = props.shape === SHAPE_RECT
  const def = props.geometry
  const width = isRect ? def.coords[1].x - def.coords[0].x : def.radius * 2
  const height = isRect ? def.coords[1].y - def.coords[0].y : def.radius * 2
  const frameWidth = width + (AREA_GUTTER * 2)
  const frameHeight = height + (AREA_GUTTER * 2)
  const borderRadius = isRect ? 0 : def.radius

  return (
    <div
      style={{
        width: frameWidth,
        height: frameHeight,
        opacity: 0.5,
        backgroundColor: def.color,
        borderRadius: `${borderRadius}px`,
        border: `solid 2px ${def.color}`
      }}
    />
  )
}

AnswerAreaDragPreview.propTypes = {
  shape: T.string.isRequired,
  geometry: T.object.isRequired
}
