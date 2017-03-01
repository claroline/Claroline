import React, {PropTypes as T} from 'react'
import {asset} from '#/main/core/asset'

export const ImageContentPlayer = (props) =>
  <div className="image-item-content">
    <img src={asset(props.item.url)} />
  </div>

ImageContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    url: T.string.isRequired
  }).isRequired
}
