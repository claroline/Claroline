import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

export const PairItemDragPreview = props => {
  return (
    <div className="drag-preview">
      {props.item.data ?
        <div dangerouslySetInnerHTML={{__html: props.item.data}}></div> :
        trans('dragging_empty_item_data', {}, 'quiz')
      }
    </div>
  )
}

PairItemDragPreview.propTypes = {
  item: T.shape({
    data: T.string.isRequired
  }).isRequired
}
