import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

// todo add placeholder when empty

const SimpleWidgetComponent = props =>
  <HtmlText>
    {props.content}
  </HtmlText>

SimpleWidgetComponent.propTypes = {
  content: T.string
}

const SimpleWidget = connect(
  (state) => ({
    content: state.content
  })
)(SimpleWidgetComponent)

export {
  SimpleWidget
}
