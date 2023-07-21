import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {FavouritesMenu as FavouritesMenuComponent} from '#/plugin/favourite/header/favourites/components/menu'

const FavouritesMenu = connect(
  (state) => ({
    isAuthenticated: securitySelectors.isAuthenticated(state)
  })
)(FavouritesMenuComponent)

export {
  FavouritesMenu
}
