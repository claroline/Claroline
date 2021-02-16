import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

const ImageDisplay = (props) => {
  if (isEmpty(props.data)) {
    return (
      <span id={props.id} className="image-display data-details-empty">{props.placeholder || trans('empty_value')}</span>
    )
  }

  if (typeof props.data === 'string') {
    // we only get the image url
    return (
      <img id={props.id} className="image-display" src={asset(props.data)} />
    )
  }

  // we get an image object
  return (
    <img id={props.id} className="image-display" src={asset(props.data.url)} />
  )
}

ImageDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string,
  placeholder: T.string
}

export {
  ImageDisplay
}
