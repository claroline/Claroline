import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

export const DefaultPreviewComponent = props =>
  <div className="drag-preview">
    {props.title || trans('dragging_empty_item_data', {}, 'quiz')}
  </div>

DefaultPreviewComponent.propTypes = {
  title: T.string
}
