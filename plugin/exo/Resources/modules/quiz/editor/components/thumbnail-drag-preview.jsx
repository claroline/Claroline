import React, {PropTypes as T} from 'react'

export const ThumbnailDragPreview = props =>
  <span className="thumbnail">
    <a className="step-title">
      {props.title}
    </a>
  </span>

ThumbnailDragPreview.propTypes = {
  title: T.string.isRequired
}
