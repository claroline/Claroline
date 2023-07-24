import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {FavouritesModal as FavouritesModalComponent} from '#/plugin/favourite/modals/favourites/components/modal'
import {actions, reducer, selectors} from '#/plugin/favourite/modals/favourites/store'

const FavouritesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
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
  )(FavouritesModalComponent)
)

export {
  FavouritesModal
}
