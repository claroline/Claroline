import React from 'react'
import {PropTypes as T} from 'prop-types'

import {tex} from '#/main/core/translation'

export const DefaultPreviewComponent = props =>
  <div className="drag-preview">
    {props.title || tex('dragging_empty_item_data')}
  </div>

DefaultPreviewComponent.propTypes = {
  title: T.string
}
