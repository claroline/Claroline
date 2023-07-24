import {trans} from '#/main/app/intl/translation'
import {SearchMenu} from '#/main/core/header/search/components/menu'

// expose main component to be used by the header
export default ({
  name: 'search',
  label: trans('search'),
  component: SearchMenu
})
