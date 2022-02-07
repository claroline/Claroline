import {trans} from '#/main/app/intl/translation'

import {ContentTab} from '#/plugin/analytics/dashboard/administration/content/containers/tab'
import {ContentOverview} from '#/plugin/analytics/dashboard/administration/content/components/overview'

export default () => ({
  name: 'content',
  order: 2,
  meta: {
    icon: 'fa fa-fw fa-folder',
    label: trans('content')
  },
  components: {
    tab: ContentTab,
    overview: ContentOverview
  }
})
