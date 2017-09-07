import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

const TextContentPlayer = (props) =>
  <HtmlText>
    {props.item.data}
  </HtmlText>

TextContentPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    data: T.string.isRequired
  }).isRequired
}

export {
  TextContentPlayer
}
