import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'

import {NavLink} from '#/main/app/router'
import {Routes} from '#/main/app/router'
import {PageContainer} from '#/main/core/layout/page'
import {RoutedPageContent} from '#/main/core/layout/router'

const PageHeader = props =>
  <header className={classes('page-header', props.className)}>
    <div>
      <h1 className="page-title">{props.title}</h1>
      <nav className="page-tabs">
        {props.tabs.map((section, sectionIndex) =>
          <NavLink
            className={classes({
              'only-icon': section.onlyIcon
            })}
            key={`section-link-${sectionIndex}`}
            to={props.path+section.path}
            exact={section.exact}
          >
            <span className={classes('page-tabs-icon', section.icon)} />
            <span className={classes({
              'page-tab-label': !section.onlyIcon,
              'sr-only': section.onlyIcon
            })}>
              {section.title}
            </span>
          </NavLink>
        )}
      </nav>
    </div>
    {props.children}
  </header>

PageHeader.propTypes = {
  className: T.string,
  title: T.string.isRequired,
  path: T.string.isRequired,
  tabs: T.arrayOf(T.shape({
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string.isRequired,
    title: T.string.isRequired,
    onlyIcon: T.bool
  })).isRequired,
  children: T.node
}

// todo add H1 (page title) and H2 (current tab)

const TabbedPage = props => {
  const displayedTabs = props.tabs.filter(tab => undefined === tab.displayed || tab.displayed)

  return (
    <PageContainer {...props} className="tabbed-page">
      <PageHeader
        title={props.title}
        path={props.path}
        tabs={displayedTabs}
      >
        <Routes
          path={props.path}
          routes={displayedTabs
            .filter(tab => !!tab.actions)
            .map((tab) => ({
              ...tab,
              component: tab.actions
            }))
          }
        />
      </PageHeader>

      <RoutedPageContent
        className="page-tab"
        path={props.path}
        routes={displayedTabs.map((tab) => ({
          path: tab.path,
          exact: tab.exact,
          component: tab.content
        }))}
        redirect={props.redirect}
      />
    </PageContainer>
  )
}

implementPropTypes(TabbedPage, PageTypes, {
  title: T.string.isRequired,
  path: T.string,
  tabs: T.arrayOf(T.shape({
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string.isRequired,
    title: T.string.isRequired,
    displayed: T.bool,
    actions: T.any
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
