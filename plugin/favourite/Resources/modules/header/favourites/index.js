import {trans} from '#/main/app/intl/translation'
import {FavouritesMenu} from '#/plugin/favourite/header/favourites/containers/menu'

// expose main component to be used by the header
export default ({
  name: 'favourites',
  label: trans('favourites', {}, 'favourite'),
  component: FavouritesMenu
})
