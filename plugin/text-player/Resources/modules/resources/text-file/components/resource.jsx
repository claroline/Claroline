import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ResourcePage} from '#/main/core/resource/containers/page.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {getPlainText} from '#/main/app/data/html/utils'

const Resource = props =>
  <ResourcePage>
    <HtmlText>{props.isHtml ? props.content : getPlainText(props.content)}</HtmlText>
  </ResourcePage>

Resource.propTypes = {
  content: T.string.isRequired,
  isHtml: T.bool.isRequired
}

const ResourceContainer = connect(
  state => ({
    content: state.content,
    isHtml: state.isHtml
  })
)(Resource)

export {
  ResourceContainer as Resource
}
