import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {trans} from '#/main/app/intl/translation'

export const OrderingItemDragPreview = props => {
  return (
    <div className="drag-preview">
      {props.data ?
        <HtmlText>{props.data}</HtmlText> :
        trans('dragging_empty_item_data', {}, 'quiz')
      }
    </div>
  )
}


OrderingItemDragPreview.propTypes = {
  data: T.string.isRequired
}
