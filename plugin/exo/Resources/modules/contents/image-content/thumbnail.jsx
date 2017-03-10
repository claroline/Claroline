import React, {PropTypes as T} from 'react'
import {asset} from '#/main/core/asset'

export const ImageContentThumbnail = (props) =>
  <div className="image-content-thumbnail">
    {props.data &&
      <img src={asset(props.data)}/>
    }
  </div>

ImageContentThumbnail.propTypes = {
  data: T.string,
  type: T.string.isRequired
}
