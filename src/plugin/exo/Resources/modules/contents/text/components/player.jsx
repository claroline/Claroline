import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentHtml} from '#/main/app/content/components/html'

const TextContentPlayer = (props) =>
  <ContentHtml>
    {props.item.data}
  </ContentHtml>

TextContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    data: T.string.isRequired
  }).isRequired
}

export {
  TextContentPlayer
}
