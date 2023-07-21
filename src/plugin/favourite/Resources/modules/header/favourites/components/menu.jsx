import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_FAVOURITES} from '#/plugin/favourite/modals/favourites'

const FavouritesMenu = (props) => {
  if (!props.isAuthenticated) {
    return null
  }

  return (
    <Button
      id="app-favourites"
      type={MODAL_BUTTON}
      className="app-header-btn app-header-item"
      icon="fa fa-fw fa-star"
      label={trans('favourites', {}, 'favourite')}
      tooltip="bottom"
      modal={[MODAL_FAVOURITES]}
    />
  )
}

FavouritesMenu.propTypes = {
  isAuthenticated: T.bool.isRequired
}

export {
  FavouritesMenu
}
