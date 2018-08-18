import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Image as ImageTypes} from '#/plugin/image-player/files/image/prop-types'

const ImagePlayer = props =>
  <img
    style={{
      marginLeft: 'auto',
      marginRight: 'auto'
    }}
    className="img-responsive"
    src={props.file.url}
    alt={props.file.hashName}
    onContextMenu={(e)=> {
      e.preventDefault()
    }}
  />

ImagePlayer.propTypes = {
  file: T.shape(
    ImageTypes.propTypes
  ).isRequired
}

export {
  ImagePlayer
}
