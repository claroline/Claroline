import React from 'react'
import {PropTypes as T} from 'prop-types'
import {asset} from '#/main/app/config/asset'

export const ImageContentPlayer = (props) =>
  <div className="image-item-content">
    <img src={asset(props.item.url)} />
  </div>

ImageContentPlayer.propTypes = {
  item: T.shape({
    url: T.string.isRequired
  }).isRequired
}
