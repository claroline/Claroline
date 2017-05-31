import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {makeModal} from '#/main/core/layout/modal'

/**
 * Container for the current page.
 *
 * Its only purpose is to initialize the flex layout to auto fill the available space.
 * As most of the time we use SPA for building UI it's a common practice to just add
 * the class `.page-container` to the mount element of the SPA.
 *
 * @param props
 * @constructor
 */
const PageContainer = props =>
  <div className="page-container">
    {props.children}
  </div>

PageContainer.propTypes = {
  /**
   * The root of the current page
   *
   * You may experience display issue (because of the flex layout)
   * if you don't use the <Page> component or an HTML container with the `.page` class.
   * For now we don't constrain it for more flexibility.
   */
  children: T.node.isRequired
}

/**
 * Root of the current page.
 *
 * We manage full screen feature here and not in the container
 * because container component may not exist (@see PageContainer doc block for more info).
 *
 * @param props
 * @constructor
 */
const Page = props =>
  React.createElement(
    !props.embedded ? 'main' : 'section', {
      className: classes('page', {
        fullscreen: props.fullscreen,
        embedded: props.embedded
      }),
      children: [
        get(props, 'modal.type') && makeModal(
          props.modal.type,
          props.modal.props,
          props.modal.fading,
          props.fadeModal,
          props.hideModal
        ),
        props.children
      ]
    }
  )

Page.propTypes = {
  fullscreen: T.bool,
  embedded: T.bool,
  children: T.node.isRequired,
  modal: T.shape({
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object.isRequired
  }),
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired
}

Page.defaultTypes = {
  /**
   * Is the page displayed in full screen ?
   */
  fullscreen: false,

  /**
   * Is the page embed into another ?
   *
   * Permits to know if we use a <main> or a <section> tag.
   */
  embedded: false
}

/**
 * Header of the current page.
 *
 * Contains title and actions.
 *
 * @param props
 * @constructor
 */
const PageHeader = props =>
  <header className={classes('page-header', props.className)}>
    <h1>
      {props.title}
      &nbsp;
      {props.subtitle && <small>{props.subtitle}</small>}
    </h1>

    {props.children}
  </header>

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

PageHeader.defaultTypes = {
  subtitle: null
}

/**
 * Content of the current page.
 *
 * Displays the passed content or an empty message if none provided.
 *
 * @param props
 * @constructor
 */
const PageContent = props =>
  <div className="page-content">
    {props.children ?
      props.children :
      <div className="placeholder">This page has no content for now.</div>
    }
  </div>

PageContent.propTypes = {
  /**
   * Content to display in the page.
   */
  children: T.node
}

export {
  PageContainer,
  Page,
  PageHeader,
  PageContent
}
