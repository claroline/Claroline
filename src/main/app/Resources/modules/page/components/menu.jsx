import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {Await} from '#/main/app/components/await'
import {Button, Toolbar} from '#/main/app/action'
import {Action, PromisedAction} from '#/main/app/action/prop-types'

const PageNav = (props) =>  {
  if (isEmpty(props.actions)) {
    return null
  }

  return (
    <ul className="nav nav-underline">
      {props.actions
        .filter(action => undefined === action.displayed || action.displayed)
        .map((action) =>
          <li className="nav-item" key={action.name || toKey(action.label)}>
            <Button
              {...action}
              className="nav-link"
              /*icon={undefined}*/
            />
          </li>
        )
      }
    </ul>
  )
}

PageNav.propTypes = {
  actions: T.arrayOf(T.shape({
    // action types
  }))
}

const PageMenu = (props) =>
  <nav className="page-nav ms-auto d-flex gap-3" role="presentation">
    {props.nav instanceof Promise ?
      <Await for={props.nav} then={(resolvedActions) => (
        <PageNav actions={resolvedActions} />
      )} /> :
      <PageNav actions={props.nav} />
    }

    {props.actions &&
      <Toolbar
        className="nav nav-underline text-shrink-0"
        buttonName="nav-link"
        toolbar={props.toolbar}
        tooltip="bottom"
        actions={props.actions}
      />
    }
  </nav>

PageMenu.propTypes = {
  /**
   * The main navigation elements.
   */
  nav: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      Action.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedAction.propTypes
    )
  ]),
  toolbar: T.string,

  /**
   * A list of additional actions.
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
  ])
}

PageMenu.defaultProps = {
  toolbar: 'more'
}

export {
  PageMenu
}
