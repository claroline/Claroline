import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/core/scaffolding/asset'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Toolbar} from '#/main/app/action/components/toolbar'

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
const PageHeader = props =>
  <header
    style={props.poster && {
      backgroundImage: `url("${asset(props.poster)}")`
    }}
    className={classes('page-header', props.className, {
      'page-poster': !!props.poster
    })}
  >
    <PageTitle
      title={props.title}
      subtitle={props.subtitle}
    />

    {props.children}

    {0 !== props.actions.length &&
      <Toolbar
        className="page-actions"
        tooltip="bottom"
        toolbar={props.toolbar}
        actions={props.actions}
      />
    }
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

  poster: T.string,

  toolbar: T.string,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),

  /**
   * Custom children.
   *
   * Add your <PageActions> or any custom component here.
   */
  children: T.node
}

PageHeader.defaultProps = {
  actions: []
}

export {
  PageHeader
}
