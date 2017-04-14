import React, {PropTypes as T} from 'react'
import {getContentDefinition} from './../content-types'

const ContentThumbnailDragPreview = props => {
  return (
    <span className="content-thumbnail">
      <span className="content-thumbnail-content">
        {React.createElement(
          getContentDefinition(props.type).thumbnail,
          {data: props.data, type: props.type}
        )}
      </span>
    </span>
  )
}

ContentThumbnailDragPreview.propTypes = {
  data: T.string,
  type: T.string.isRequired
}

export {ContentThumbnailDragPreview}
