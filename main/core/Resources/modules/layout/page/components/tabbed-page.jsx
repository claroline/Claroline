import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'

import {Router, Route, Redirect, NavLink, Switch} from '#/main/core/router'
import {Page, PageContent} from '#/main/core/layout/page/components/page.jsx'

const PageTabs = props =>
  <header className={classes('page-header', props.className)}>
    <nav className="page-tabs">
      {props.tabs.map((section, sectionIndex) =>
        <NavLink
          key={`section-link-${sectionIndex}`}
          to={section.path}
          exact={section.exact}
        >
          <span className={classes('page-tabs-icon', section.icon)} />
          {section.title}
        </NavLink>
      )}
    </nav>

    {props.children}
  </header>

PageTabs.propTypes = {
  className: T.string,
  tabs: T.arrayOf(T.shape({
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string.isRequired,
    title: T.string.isRequired
  })).isRequired,
  children: T.node
}

// todo add H1 (page title) and H2 (current tab)

const TabbedPage = props =>
  <Router>
    <Page
      {...props}
    >
      <PageTabs
        tabs={props.tabs}
      >
        <Switch>
          {props.tabs.map((tab, tabIndex) =>
            <Route
              {...tab}
              key={`tab-actions-${tabIndex}`}
              component={tab.actions}
            />
          )}
        </Switch>
      </PageTabs>

      <PageContent className="page-tab">
        <Switch>
          {props.tabs.map((tab, tabIndex) =>
            <Route
              {...tab}
              key={`tab-actions-${tabIndex}`}
              component={tab.content}
            />
          )}

          {props.redirect.map((redirect, redirectIndex) =>
            <Redirect
              {...redirect}
              key={`tab-redirect-${redirectIndex}`}
            />
          )}
        </Switch>
      </PageContent>
    </Page>
  </Router>

implementPropTypes(TabbedPage, PageTypes, {
  tabs: T.arrayOf(T.shape({
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string.isRequired,
    title: T.string.isRequired
  })).isRequired,
  redirect: T.arrayOf(T.shape({
    exact: T.bool,
    from: T.string.isRequired,
    to: T.string.isRequired
  }))
}, {
  redirect: []
})

export {
  TabbedPage
}
