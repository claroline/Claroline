import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {Route as RouteTypes} from '#/main/app/router/prop-types'
import {PageContent} from '#/main/core/layout/page'

const RoutedPageContent = props =>
  <PageContent
    headerSpacer={props.headerSpacer}
    className={props.className}
  >
    <Routes {...props} />
  </PageContent>

RoutedPageContent.propTypes = {
  className: T.string,
  headerSpacer: T.bool,

  // todo : reuse propTypes from Routes
  path: T.string,
  exact: T.bool,
  routes: T.arrayOf(
    T.shape(RouteTypes.propTypes).isRequired
  ).isRequired,
  redirect: T.arrayOf(T.shape({
    from: T.string.isRequired,
    to: T.string.isRequired,
    exact: T.bool
  }))
}

export {
  RoutedPageContent
}
