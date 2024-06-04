import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Image as ImageTypes} from '#/plugin/image-player/files/image/prop-types'
import {ResourcePage} from '#/main/core/resource'

const ImagePlayer = props =>
  <ResourcePage root={true}>
    <img
      style={{
        marginLeft: 'auto',
        marginRight: 'auto'
      }}
      className="img-fluid"
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
