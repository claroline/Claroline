import React from 'react'
import classes from 'classnames'

import {asset} from '#/main/core/scaffolding/asset'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'

const PageWrapper = props => !props.embedded ?
  <main className={classes('page main', props.className)}>
    {props.children}
  </main> :
  <section className={classes('page embedded', props.className)}>
    {props.children}
  </section>

PageWrapper.propTypes = {
  className: T.string,
  embedded: T.bool.isRequired,
  children: T.node
}

/**
 * Root of the current page.
 *
 * For now, modals are managed here.
 * In future version, when the layout will be in React,
 * it'll be moved in higher level.
 */
const Page = props =>
  <PageWrapper
    embedded={props.embedded}
    className={classes(props.className, {
      fullscreen: props.fullscreen
    })}
  >
    {props.children}
  </PageWrapper>

implementPropTypes(Page, PageTypes, {
  children: T.node.isRequired
})

/**
 * Title of the current page.
 */
const PageTitle = props =>
  <h1 className="page-title">
    {props.title}
    {props.subtitle &&
      <small>{props.subtitle}</small>
    }
  </h1>

PageTitle.propTypes = {
  /**
   * The title of the current page.
   */
  title: T.string.isRequired,

  /**
   * An optional sub title.
   *
   * Mostly used when the current page has sub-sections
   * example : in quizzes, we have edit/play/papers/etc. sections
   */
  subtitle: T.string
}

/**
 * Header of the current page.
 *
 * Contains title, actions and an optional poster image.
 */
const PageHeader = props => {
  let styles
  if (props.poster) {
    styles = {
      backgroundImage: `url("${asset(props.poster)}")`
    }
  }

  return (
    <header
      style={styles}
      className={classes('page-header', props.className, {
        'page-poster': !!props.poster
      })}
    >
      <PageTitle
        title={props.title}
        subtitle={props.subtitle}
      />

      {props.children}
    </header>
  )
}

PageHeader.propTypes = {
  /**
   * The title of the current page.
   */
  title: T.string.isRequired,

  /**
   * An optional sub title.
   *
   * Mostly used when the current page has sub-sections
   * example : in quizzes, we have edit/play/papers/etc. sections
   */
  subtitle: T.string,

  poster: T.string,

  /**
   * Additional classes to add to the header tag.
   */
  className: T.string,

  /**
   * Custom children.
   *
   * Add your <PageActions> or any custom component here.
   */
  children: T.node
}

/**
 * Content of the current page.
 */
const PageContent = props =>
  <div className={classes('page-content', props.className, {
    'page-content-shift': props.headerSpacer
  })}>
    {props.children}
  </div>

PageContent.propTypes = {
  className: T.string,
  headerSpacer: T.bool,

  /**
   * Content to display in the page.
   */
  children: T.node.isRequired
}

PageContent.defaultProps = {
  headerSpacer: true
}

export {
  Page,
  PageHeader,
  PageContent
}
