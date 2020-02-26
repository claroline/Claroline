import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'

const TextContentThumbnail = props =>
  <ContentHtml className="text-content-thumbnail">
    {props.data}
  </ContentHtml>

TextContentThumbnail.propTypes = {
  data: T.string
}

export {
  TextContentThumbnail
}
