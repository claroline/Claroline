import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'

const TextContentThumbnail = props =>
  <HtmlText className="text-content-thumbnail">
    {props.data}
  </HtmlText>

TextContentThumbnail.propTypes = {
  data: T.string
}

export {
  TextContentThumbnail
}
