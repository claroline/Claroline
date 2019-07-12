import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

const HistoryDropdown = () =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    <ul className="nav nav-tabs">
      <li className="active">
        <a
          role="button"
          href=""
          onClick={(e) => {
            e.preventDefault()
          }}
        >
          {trans('workspaces')}
        </a>
      </li>
      <li>
        <a
          role="button"
          href=""
          onClick={(e) => {
            e.preventDefault()
          }}
        >
          {trans('resources')}
        </a>
      </li>
    </ul>
  </div>

HistoryDropdown.propTypes = {

}

const HistoryMenu = (props) => {
  if (!props.isAuthenticated) {
    return null
  }

  return (
    <Button
      id="app-history"
      type={MENU_BUTTON}
      className="app-header-btn app-header-item"
      icon="fa fa-fw fa-history"
      label={trans('history')}
      tooltip="bottom"
      menu={
        <HistoryDropdown

        />
      }
    />
  )
}

HistoryMenu.propTypes = {
  isAuthenticated: T.bool.isRequired,
  history: T.arrayOf(T.shape({

  }))
}

export {
  HistoryMenu
}
