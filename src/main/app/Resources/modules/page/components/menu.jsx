import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {toKey} from '#/main/core/scaffolding/text'
import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action'
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
  <nav className="page-nav ms-auto d-flex gap-3">
    {props.actions instanceof Promise ?
      <Await for={props.actions} then={(resolvedActions) => (
        <PageNav actions={resolvedActions} />
      )} /> :
      <PageNav actions={props.actions} />
    }

    {props.children}
  </nav>

PageMenu.propTypes = {
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
  children: T.any
}

export {
  PageMenu
}
