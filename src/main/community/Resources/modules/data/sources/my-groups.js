import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/group/routing'
import {GroupCard} from '#/main/community/group/components/card'

export default {
  name: 'my-groups',
  icon: 'fa fa-fw fa-users',
  parameters: {
    primaryAction: (group) => ({
      type: URL_BUTTON,
      target: '#' + route(group)
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      }, {
        name: 'organizations',
        label: trans('organizations'),
        type: 'organizations',
        displayed: false,
        displayable: false,
        sortable: false
      }
    ],
    card: GroupCard
  }
}
