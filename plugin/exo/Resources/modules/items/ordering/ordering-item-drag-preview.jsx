import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/core/translation'

export const OrderingItemDragPreview = props => {
  return (
    <div className="drag-preview">
      {props.data ?
        <div dangerouslySetInnerHTML={{__html: props.data}} />
        :
        tex('dragging_empty_item_data')
      }
    </div>
  )
}


OrderingItemDragPreview.propTypes = {
  data: T.string.isRequired
}
