import {createElement} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {declareBadgeRule} from '#/plugin/open-badge/badge-rule'

import {route} from '#/plugin/cursus/event/routing'
import {route as contextRoute} from '#/main/app/context'

export default declareBadgeRule({
  name: 'training_event_presence',
  meta: {
    label: trans('training_event_presence', {}, 'badge')
  },
  render: (rule, contextType, contextId) => createElement(Html, {
    children: trans('training_event_presence_desc', {
      training_event: `<a class="fw-bolder text-reset" href="#${route(rule.subject, contextRoute(contextType, contextId))}">`+get(rule, 'subject.name')+'</a>'
    }, 'badge')
  }),
  default: (rule) => Object.assign({}, rule, {
    subjectClass: 'Claroline\\CursusBundle\\Entity\\Event'
  }),
  configure: (contextType, contextId) => [
    {
      name: 'subject',
      type: 'training_event',
      label: trans('session_event', {}, 'cursus'),
      required: true,
      options: {
        contextType: contextType,
        contextId: contextId
      }
    }
  ]
})
