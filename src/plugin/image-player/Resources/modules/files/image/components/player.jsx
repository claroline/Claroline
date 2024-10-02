import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Image as ImageTypes} from '#/plugin/image-player/files/image/prop-types'
import {ResourcePage} from '#/main/core/resource'

const ImagePlayer = props =>
  <ResourcePage>
    <img
      className="img-fluid m-auto"
      src={props.file.url}
      alt={props.file.hashName}
      onContextMenu={(e)=> {
        e.preventDefault()
      }}
    />
  </ResourcePage>

ImagePlayer.propTypes = {
  file: T.shape(
    ImageTypes.propTypes
  ).isRequired
}

export {
  ImagePlayer
}
