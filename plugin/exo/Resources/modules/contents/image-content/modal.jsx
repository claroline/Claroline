import React, {PropTypes as T} from 'react'
import {asset} from '#/main/core/asset'

export const ImageContentModal = (props) =>
  <div className="image-content-modal">
    {props.data &&
      <img src={asset(props.data)}/>
    }
  </div>

ImageContentModal.propTypes = {
  data: T.string,
  type: T.string.isRequired
}
