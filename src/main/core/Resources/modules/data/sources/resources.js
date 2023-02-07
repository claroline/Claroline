import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/resource/routing'
import {getTypes} from '#/main/core/resource/utils'
import {ResourceCard} from '#/main/core/resource/components/card'

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
        type: 'string',
        displayed: true,
        primary: true
      }, {
        name: 'code',
        label: trans('code'),
        type: 'string'
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
        label: trans('directory', {}, 'resource'),
        type: 'resource'
      }, {
        name: 'meta.views',
        type: 'number',
        label: trans('views')
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
        name: 'meta.creator',
        type: 'user',
        label: trans('creator'),
        displayed: true
      }, {
        name: 'evaluation.estimatedDuration',
        label: trans('estimated_duration'),
        type: 'number',
        options: {
          unit: trans('minutes')
        },
        alias: 'estimatedDuration'
      }, {
        name: 'evaluation.required',
        label: trans('required_resource', {}, 'resource'),
        type: 'boolean',
        alias: 'required'
      }, {
        name: 'evaluation.evaluated',
        label: trans('evaluated_resource', {}, 'resource'),
        type: 'boolean',
        alias: 'evaluated'
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        displayable: true,
        sortable: false,
        options: {
          objectClass: 'Claroline\\CoreBundle\\Entity\\Resource\\ResourceNode'
        }
      }
    ],
    card: ResourceCard
  }
}
