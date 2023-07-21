/**
 * Favourites modal.
 * Displays the favourites (workspaces and resources) of the current user.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {FavouritesModal} from '#/plugin/favourite/modals/favourites/containers/modal'

const MODAL_FAVOURITES = 'MODAL_FAVOURITES'

// make the modal available for use
registry.add(MODAL_FAVOURITES, FavouritesModal)

export {
  MODAL_FAVOURITES
}
