import {trans} from '#/main/app/intl/translation'
import {HistoryMenu} from '#/plugin/history/header/history/containers/menu'

// expose main component to be used by the header
export default ({
  name: 'history',
  label: trans('history', {}, 'history'),
  component: HistoryMenu
})
