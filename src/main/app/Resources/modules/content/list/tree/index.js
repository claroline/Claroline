
import {trans} from '#/main/app/intl'
import {TreeData} from '#/main/app/content/list/tree/components/data'

import {constants} from '#/main/app/content/list/tree/constants'

/**
 * Tree view modes for the ListData component
 */
export default {
  [constants.DISPLAY_TREE]: {
    icon: 'fa fa-fw fa-sitemap',
    label: trans('list_display_tree'),
    component: TreeData,
    options: {
      useCard: true, // it uses card representation for rendering data
      size: 'sm',
      orientation: 'row'
    }
  }
}
