import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {toKey} from '#/main/core/scaffolding/text'

import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'
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
  subtitle: T.node
}

/**
 * Header of the current page.
 *
 * Contains title, icon, actions and an optional poster image.
 */
const PageHeader = props =>
  <header
    style={props.poster && {
      backgroundImage: `url("${asset(props.poster)}")`
    }}
    className={classes('page-header', {
      'page-poster': !!props.poster
    })}
  >
    <PageTitle
      title={props.title}
      subtitle={props.subtitle}
    />

    {props.icon &&
      <div className="page-icon">
        {props.icon}
      </div>
    }

    {props.children}

    {(!isEmpty(props.actions) || props.actions instanceof Promise) &&
      <Toolbar
        id={toKey(props.title)}
        className="page-actions"
        tooltip="bottom"
        toolbar={props.toolbar}
        actions={props.actions}
        disabled={props.disabled}
        scope="object"
      />
    }
  </header>

PageHeader.propTypes = {
  title: T.string.isRequired,
  subtitle: T.node,
  icon: T.oneOfType([T.string, T.element]),
  poster: T.string,
  disabled: T.bool,
  toolbar: T.string,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),

  /**
   * Custom children.
   *
   * Add your custom component here.
   */
  children: T.node
}

PageHeader.defaultProps = {
  actions: []
}

export {
  PageHeader
}
