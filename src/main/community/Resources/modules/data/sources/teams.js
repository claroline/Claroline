import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/community/team/routing'
import {TeamCard} from '#/main/community/team/components/card'

export default {
  name: 'teams',
  icon: 'fa fa-fw fa-users',
  parameters: {
    primaryAction: (team) => ({
      type: URL_BUTTON,
      target: '#' + route(team)
    }),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {long: true},
        displayed: true,
        sortable: false
      }, {
        name: 'users',
        type: 'number',
        label: trans('users'),
        displayed: true,
        filterable: false
      }, {
        name: 'directory',
        type: 'resource',
        label: trans('directory', {}, 'resource'),
        sortable: false,
        filterable: false
      }, {
        name: 'registration.selfRegistration',
        label: trans('public_registration'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }, {
        name: 'registration.selfUnregistration',
        label: trans('public_unregistration'),
        displayed: false,
        filterable: true,
        type: 'boolean'
      }
    ],
    card: TeamCard
  }
}
