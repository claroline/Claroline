import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Router} from '#/main/app/router'

const PageWrapper = props =>
  <Router embedded={props.embedded}>
    {React.createElement(!props.embedded ? 'main':'section', {
      className: classes('page', props.className)
    }, props.children)}
  </Router>

PageWrapper.propTypes = {
  className: T.string,
  embedded: T.bool.isRequired,
  children: T.node
}

export {
  PageWrapper
}
