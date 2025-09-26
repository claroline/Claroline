import {createElement} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {declareBadgeRule} from '#/plugin/open-badge/badge-rule'

import {route} from '#/main/community/role/routing'
import {route as contextRoute} from '#/main/app/context'

export default declareBadgeRule({
  name: 'in_role',
  meta: {
    label: trans('in_role', {}, 'badge')
  },
  render: (rule, contextType, contextId) => createElement(Html, {
    children: trans('in_role_desc', {
      role: `<a class="fw-bolder text-reset" href="#${route(rule.subject, contextRoute(contextType, contextId, 'community'))}">`+trans(get(rule, 'subject.translationKey'))+'</a>'
    }, 'badge')
  }),
  default: (rule) => Object.assign({}, rule, {
    subjectClass: 'Claroline\\CoreBundle\\Entity\\Role'
  }),
  configure: (contextType, contextId) => [
    {
      name: 'subject',
      type: 'role',
      label: trans('role', {}, 'community'),
      required: true,
      options: {
        picker: {
          contextType: contextType,
          contextId: contextId
        }
      }
    }
  ]
})
