import {ResourceCard} from '#/main/core/resource/components/card'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource/routing'
import {getTypes} from '#/main/core/resource/utils'

export default {
  name: 'resources',
  icon: 'fa fa-fw fa-folder',
  parameters: {
    primaryAction: (resourceNode) => ({
      type: URL_BUTTON,
      target: `#${route(resourceNode)}`
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'meta.type',
        alias: 'resourceType',
        label: trans('type'),
        displayed: true,
        type: 'choice',
        options: {
          choices: getTypes()
            .sort((a, b) => trans(a.name, {}, 'resource') >= trans(b.name, {}, 'resource') ? 1 : -1)
            .reduce((resourceTypes, current) => Object.assign(resourceTypes, {[current.name]: trans(current.name, {}, 'resource')}), {}),
          condensed: true
        }
      }, {
        name: 'parent',
        label: trans('parent', {}, 'resource'),
        type: 'resource'
      }, {
        name: 'meta.views',
        type: 'number',
        label: trans('views')
      }, { // todo : find a way to display it only to those who have 'administrate' right
        name: 'meta.published',
        alias: 'published',
        type: 'boolean',
        label: trans('published'),
        displayed: true
      }, {
        name: 'meta.created',
        label: trans('creation_date'),
        type: 'date',
        alias: 'creationDate',
        displayed: true
      }, {
        name: 'meta.updated',
        label: trans('modification_date'),
        type: 'date',
        alias: 'modificationDate',
        displayed: true
      }, {
        name: 'meta.creator',
        type: 'user',
        label: trans('creator'),
        displayed: true
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        displayable: false,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CoreBundle\\Entity\\Resource\\ResourceNode'
        }
      }
    ],
    card: ResourceCard
  }
}
