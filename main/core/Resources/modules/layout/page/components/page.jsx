import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {makeModal} from '#/main/core/layout/modal'

/**
 * Root of the current page.
 *
 * For now, modals are managed here.
 * In future version, when the layout will be in React,
 * it'll be moved in higher level.
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
  fadeModal: T.func,
  hideModal: T.func
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
    <h1 className="page-title">
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
  Page,
  PageHeader,
  PageContent
}
