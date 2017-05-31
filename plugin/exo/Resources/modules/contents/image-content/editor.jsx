import React from 'react'
import {PropTypes as T} from 'prop-types'
import {asset} from '#/main/core/asset'

export const ImageContent = (props) =>
  <div className="image-item-content">
    <img src={(props.item.data && asset(props.item.data)) || (props.item.url && asset(props.item.url)) || ''} />
  </div>

ImageContent.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    data: T.string,
    url: T.string
  }).isRequired
}
