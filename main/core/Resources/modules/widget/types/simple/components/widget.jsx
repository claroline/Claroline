import React from 'react'
import {PropTypes as T} from 'prop-types'

import {HtmlText} from '#/main/core/layout/components/html-text'

// todo add placeholder when empty

const SimpleWidget = props =>
  <HtmlText>{props.content}</HtmlText>

SimpleWidget.propTypes = {
  content: T.string
}

export {
  SimpleWidget
}
