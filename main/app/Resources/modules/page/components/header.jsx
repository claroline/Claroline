import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/core/scaffolding/asset'

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
  subtitle: T.string
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

    {(0 !== props.actions.length || props.actions instanceof Promise) &&
      <Toolbar
        className="page-actions"
        tooltip="bottom"
        toolbar={props.toolbar}
        actions={props.actions}
        scope="object"
        topbar={true}
      />
    }
  </header>

PageHeader.propTypes = {
  title: T.string.isRequired,
  subtitle: T.string,
  icon: T.oneOfType([T.string, T.element]),
  poster: T.string,
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
  ])
}

PageHeader.defaultProps = {
  actions: []
}

export {
  PageHeader
}
