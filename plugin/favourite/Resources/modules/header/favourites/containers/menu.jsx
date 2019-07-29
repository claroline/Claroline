import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {FavouritesMenu as FavouritesMenuComponent} from '#/plugin/favourite/header/favourites/components/menu'
import {actions, reducer, selectors} from '#/plugin/favourite/header/favourites/store'

const FavouritesMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isAuthenticated: securitySelectors.isAuthenticated(state),
      loaded: selectors.loaded(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      getFavourites() {
        dispatch(actions.getFavourites())
      },
      deleteFavourite(object, type) {
        dispatch(actions.deleteFavourite(object, type))
      }
    })
  )(FavouritesMenuComponent)
)

export {
  FavouritesMenu
}
