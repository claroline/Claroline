import React from 'react'
import get from 'lodash/get'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {makeModal} from '#/main/core/layout/modal'
import {Page as PageTypes} from '#/main/core/layout/page/prop-types'

import {FlyingAlerts} from '#/main/core/layout/alert/components/flying-alerts.jsx'

const PageWrapper = props => !props.embedded ?
  <main className={props.className}>
    {props.children}
  </main> :
  <section className={props.className}>
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
 *
 * @param props
 * @constructor
 */
const Page = props =>
  <PageWrapper
    embedded={props.embedded}
    className={classes('page', props.className, {
      fullscreen: props.fullscreen,
      embedded: props.embedded
    })}
  >
    {props.alerts &&
      <FlyingAlerts alerts={props.alerts} removeAlert={props.removeAlert}/>
    }

    {get(props, 'modal.type') && makeModal(
      props.modal.type,
      props.modal.props,
      props.modal.fading,
      props.fadeModal,
      props.hideModal
    )}

    {props.children}
  </PageWrapper>

implementPropTypes(Page, PageTypes, {
  children: T.node.isRequired
})

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
  <div className={classes('page-content', props.className)}>
    {props.children ?
      props.children :
      <div className="placeholder">This page has no content for now.</div>
    }
  </div>

PageContent.propTypes = {
  className: T.string,
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
