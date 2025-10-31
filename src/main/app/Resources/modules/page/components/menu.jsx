import React, {createElement, useId} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button, constants, pickActionSet, Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Action, PromisedAction} from '#/main/app/action/prop-types'
import {selectors as securitySelectors} from '#/main/app/security/store'

const PageBreadcrumb = props => {
  const items = props.breadcrumb
    .filter(item => undefined === item.displayed || item.displayed)

  if (0 !== items.length) {
    return (
      <nav aria-label={trans('breadcrumb')} className="mx-n2 my-2">
        <ol className="breadcrumb d-inline-flex flex-row flex-nowrap align-items-center list-unstyled mb-0">
          {items
            .filter(item => undefined === item.displayed || item.displayed)
            .map((item) =>
              <li key={item.label} className="d-inline-flex align-items-center breadcrumb-item">
                <Button
                  className="text-body py-1 px-2 rounded-1 fs-sm focus-ring text-truncate"
                  type={LINK_BUTTON}
                  exact={true}
                  {...omit(item, 'displayed')}
                />
              </li>
            )
          }
        </ol>
      </nav>
    )
  }

  return null
}

PageBreadcrumb.propTypes = {
  className: T.string,
  breadcrumb: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  }))
}

const PageMenu = (props) => {
  const isAuthenticated = useSelector(securitySelectors.isAuthenticated)

  let displayedNav = []
  if (!isEmpty(props.nav)) {
    displayedNav = props.nav
      .filter(action => undefined === action.displayed || action.displayed)
  }
  let actions = []
  if (props.actions) {
    actions = pickActionSet(constants.ACTION_SET_DETAILS, props.actions)
  }

  const toolMenuTitleId = useId()

  return (
    <div className={classes('app-page-menu px-4 d-flex gap-4 flex-nowrap align-items-center bg-body z-3')} role="presentation">
      {!props.embedded && props.affix && createElement(props.affix, {
        breadcrumb: props.breadcrumb
      })}

      {!isEmpty(props.breadcrumb) &&
        <PageBreadcrumb breadcrumb={props.breadcrumb} />
      }

      {props.children}

      {(!isEmpty(displayedNav) || actions) &&
        <nav
          className="app-tool-menu ms-auto d-flex flex-nowrap gap-4 fs-sm overflow-hidden"
          aria-labelledby={toolMenuTitleId}
        >
          <h2 id={toolMenuTitleId} className="visually-hidden">{trans('tool_menu')}</h2>
          {!isEmpty(displayedNav) &&
            <ul className="nav nav-underline flex-nowrap overflow-hidden">
              {displayedNav.map((nav) =>
                <li className="nav-item" key={nav.name || nav.label}>
                  <Button
                    {...nav}
                    icon={nav.icon ? classes(nav.icon, 'lh-base') : undefined}
                    className="nav-link text-nowrap"
                  />
                </li>
              )}
            </ul>
          }

          {actions &&
            <Toolbar
              className="d-flex gap-1 mx-n2 my-2"
              buttonName="btn btn-text-body focus-ring py-1 px-2 rounded-1 border-0 fs-sm"
              toolbar={props.toolbar || 'more'}
              tooltip="bottom"
              actions={actions}
              moreIcon="fa-ellipsis-h"
            />
          }
        </nav>
      }

      {!props.embedded && !isAuthenticated &&
        <Button
          className={classes('btn btn-primary my-auto fs-sm me-n3', {
            'ms-auto': isEmpty(displayedNav) && isEmpty(actions)
          })}
          type={LINK_BUTTON}
          label={trans('login')}
          target="/login"
        />
      }
    </div>
  )
}

PageMenu.propTypes = {
  embedded: T.bool.isRequired,

  affix: T.elementType,
  breadcrumb: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),

  /**
   * The main navigation elements.
   */
  nav: T.arrayOf(T.shape(
    Action.propTypes
  )),
  toolbar: T.string,

  /**
   * A list of actions.
   */
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      Action.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedAction.propTypes
    )
  ]),
  children: T.node
}

export {
  PageMenu
}
