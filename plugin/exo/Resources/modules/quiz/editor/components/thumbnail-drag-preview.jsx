import React from 'react'
import {PropTypes as T} from 'prop-types'

export const ThumbnailDragPreview = props =>
  <span className="thumbnail">
    <a className="step-title">
      {props.title}
    </a>
  </span>

ThumbnailDragPreview.propTypes = {
  title: T.string.isRequired
}
