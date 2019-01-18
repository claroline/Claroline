import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const PageWrapper = props => React.createElement(!props.embedded ? 'main':'section', {
  className: classes('page', props.className)
}, props.children)

PageWrapper.propTypes = {
  className: T.string,
  embedded: T.bool.isRequired,
  children: T.node
}

export {
  PageWrapper
}
