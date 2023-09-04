import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/role/routing'
import {RoleCard} from '#/main/community/role/components/card'

export default {
  name: 'my-roles',
  icon: 'fa fa-fw fa-chess',
  parameters: {
    primaryAction: (role) => ({
      type: URL_BUTTON,
      target: '#' + route(role)
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'type',
        type: 'choice',
        label: trans('type'),
        displayed: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      }, {
        name: 'workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true
      }, {
        name: 'user',
        type: 'user',
        label: trans('user'),
        filterable: false
      }
    ],
    card: RoleCard
  }
}
