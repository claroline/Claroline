import {trans} from '#/main/app/intl/translation'

import {CommunityTab} from '#/plugin/analytics/dashboard/administration/community/containers/tab'
import {CommunityOverview} from '#/plugin/analytics/dashboard/administration/community/components/overview'

export default () => ({
  name: 'community',
  order: 3,
  meta: {
    icon: 'fa fa-fw fa-users',
    label: trans('community')
  },
  components: {
    tab: CommunityTab,
    overview: CommunityOverview
  }
})
