import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'
import {trans} from '#/main/app/intl/translation'

export const OrderingItemDragPreview = props => {
  return (
    <div className="drag-preview">
      {props.data ?
        <ContentHtml>{props.data}</ContentHtml> :
        trans('dragging_empty_item_data', {}, 'quiz')
      }
    </div>
  )
}


OrderingItemDragPreview.propTypes = {
  data: T.string.isRequired
}
