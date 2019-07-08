import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {FavouritesMenu as FavouritesMenuComponent} from '#/plugin/favourite/header/favourites/components/menu'
import {actions, reducer, selectors} from '#/plugin/favourite/header/favourites/store'

const FavouritesMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      loadMenu() {
        dispatch(actions.fetchMenu())
      }
    })
  )(FavouritesMenuComponent)
)

export {
  FavouritesMenu
}
