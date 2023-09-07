import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/organization/routing'
import {OrganizationCard} from '#/main/community/organization/components/card'

export default {
  name: 'organizations',
  icon: 'fa fa-fw fa-building',
  parameters: {
    primaryAction: (organization) => ({
      type: URL_BUTTON,
      target: '#' + route(organization)
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
        label: trans('code')
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      },{
        name: 'meta.default',
        type: 'boolean',
        label: trans('default')
      }, {
        name: 'email',
        type: 'email',
        label: trans('email')
      }, {
        name: 'parent',
        type: 'organization',
        label: trans('parent')
      }, {
        name: 'restrictions.public',
        alias: 'public',
        type: 'boolean',
        label: trans('public')
      }
    ],
    card: OrganizationCard
  }
}
