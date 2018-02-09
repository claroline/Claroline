import React from 'react'

import {Router, Routes} from '#/main/core/router'
import {Page, PageContent} from '#/main/core/layout/page'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'
import {Route as RouteTypes} from '#/main/core/router/prop-types'

const RoutedPage = props =>
  <Router>
    <Page {...props}>
      {props.children}
    </Page>
  </Router>

implementPropTypes(RoutedPage, PageTypes, {
  children: T.node.isRequired
})

const RoutedPageContent = props =>
  <PageContent className={props.className}>
    <Routes {...props} />
  </PageContent>

RoutedPageContent.propTypes = {
  className: T.string,

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
  RoutedPage,
  RoutedPageContent
}
