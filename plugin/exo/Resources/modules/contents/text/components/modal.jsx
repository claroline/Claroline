import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'

const TextContentModal = (props) =>
  <HtmlText className="text-content-modal">
    {props.data}
  </HtmlText>

TextContentModal.propTypes = {
  data: T.string,
  type: T.string.isRequired
}

export {
  TextContentModal
}
