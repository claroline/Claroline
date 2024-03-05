import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Button} from '#/main/app/action'
import {toKey} from '#/main/core/scaffolding/text'
import isEmpty from 'lodash/isEmpty'

const PageMenu = (props) =>
  <nav className="page-nav ms-auto d-flex gap-3">
    {!isEmpty(props.actions) &&
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
    }

    {props.children}
  </nav>

PageMenu.propTypes = {
  actions: T.arrayOf(T.shape({
    // action types
  }))
}

export {
  PageMenu
}
