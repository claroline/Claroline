import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

const FavouritesDropdown = () =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    FAVORITES
  </div>

FavouritesDropdown.propTypes = {

}

const FavouritesMenu = (props) => {
  if (!props.isAuthenticated) {
    return null
  }

  return (
    <Button
      id="app-favorites"
      type={MENU_BUTTON}
      className="app-header-btn app-header-item"
      icon="fa fa-fw fa-star"
      label={trans('favourites', {}, 'favourite')}
      tooltip="bottom"
      menu={
        <FavouritesDropdown

        />
      }
    />
  )
}

FavouritesMenu.propTypes = {
  isAuthenticated: T.bool.isRequired
}

export {
  FavouritesMenu
}
