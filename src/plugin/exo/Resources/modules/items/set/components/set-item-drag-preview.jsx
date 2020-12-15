import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

export const SetItemDragPreview = props => {
  return (
    <div className="drag-preview">
      {props.item.data ?
        <div dangerouslySetInnerHTML={{__html: props.item.data}}></div> :
        trans('dragging_empty_item_data', {}, 'quiz')
      }
    </div>
  )
}

SetItemDragPreview.propTypes = {
  item: T.shape({
    data: T.string.isRequired
  }).isRequired
}
