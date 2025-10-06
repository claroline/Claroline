import {createElement} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {declareBadgeRule} from '#/plugin/open-badge/badge-rule'

import {route} from '#/main/core/resource'

export default declareBadgeRule({
  name: 'resource_progression',
  meta: {
    label: trans('resource_progression', {}, 'badge')
  },
  render: (rule) => createElement(Html, {
    children: trans('resource_progression_desc', {
      progression: `<b>${get(rule, 'data.value', '')}%</b>`,
      resource: `<a class="fw-bolder text-reset" href="#${route(rule.subject)}">`+get(rule, 'subject.name')+'</a>'
    }, 'badge')
  }),
  default: (rule) => Object.assign({}, rule, {
    subjectClass: 'Claroline\\CoreBundle\\Entity\\Resource\\ResourceNode'
  }),
  configure: (contextType, contextId) => [
    {
      name: 'subject',
      type: 'resource',
      label: trans('resource'),
      required: true,
      options: {
        picker: {
          contextType: contextType,
          contextId: contextId
        }
      }
    }, {
      name: 'data.value',
      type: 'number',
      label: trans('progression'),
      required: true,
      options: {
        min: 0,
        max: 100,
        unit: '%'
      }
    }
  ]
})
