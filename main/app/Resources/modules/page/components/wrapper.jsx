import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const PageWrapper = props => React.createElement(!props.embedded ? 'main':'section', {
  id: props.id,
  className: classes('page', props.className)
}, props.children)

PageWrapper.propTypes = {
  id: T.string,
  className: T.string,
  embedded: T.bool.isRequired,
  children: T.node
}

export {
  PageWrapper
}
