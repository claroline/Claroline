import React, {PropTypes as T} from 'react'
import {tex} from '#/main/core/translation'

export const SetItemDragPreview = props => {
  return (
    <div className="drag-preview">
      {props.item.data ?
        <div dangerouslySetInnerHTML={{__html: props.item.data}}></div>
        :
        tex('dragging_empty_item_data')
      }
    </div>
  )
}

SetItemDragPreview.propTypes = {
  item: T.shape({
    data: T.string.isRequired
  }).isRequired
}
