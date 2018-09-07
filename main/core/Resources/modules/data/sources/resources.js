
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

import {trans} from '#/main/core/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {getTypes} from '#/main/core/resource/utils'

export default {
  name: 'resources',
  icon: 'fa fa-fw fa-folder',
  parameters: {
    primaryAction: (resourceNode) => ({ // todo : reuse resource default action
      type: URL_BUTTON,
      target: [ 'claro_resource_show', {
        type: resourceNode.meta.type,
        id: resourceNode.id
      }]
    }),
    definition: [
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
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
        name: 'resourceType',
        label: trans('type'),
        type: 'choice',
        options: {
          choices: getTypes().reduce((resourceTypes, current) => Object.assign(resourceTypes, {[current.name]: trans(current.name, {}, 'resource')}), {}),
          condensed: true
        },
        displayable: false,
        filterable: true
      }
    ],
    card: ResourceCard
  }
}
