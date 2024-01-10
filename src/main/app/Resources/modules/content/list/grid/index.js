
import {trans} from '#/main/app/intl'
import {GridData} from '#/main/app/content/list/grid/components/data'

import {constants} from '#/main/app/content/list/grid/constants'

/**
 * Grid view modes for the ListData component
 */
export default {
  [constants.DISPLAY_LIST_SM]: {
    icon: 'fa fa-fw fa-list-ul',
    label: trans('list_display_list_sm'),
    component: GridData,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'sm',
      orientation: 'row'
    }
  },
  [constants.DISPLAY_LIST]: {
    icon: 'fa fa-fw fa-align-justify',
    label: trans('list_display_list'),
    component: GridData,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'lg',
      orientation: 'row'
    }
  },
  [constants.DISPLAY_TILES_SM]: {
    icon: 'fa fa-fw fa-th',
    label: trans('list_display_tiles_sm'),
    component: GridData,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'sm',
      orientation: 'col'
    }
  },
  [constants.DISPLAY_TILES]: {
    icon: 'fa fa-fw fa-th-large',
    label: trans('list_display_tiles'),
    component: GridData,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'lg',
      orientation: 'col'
    }
  }
}
