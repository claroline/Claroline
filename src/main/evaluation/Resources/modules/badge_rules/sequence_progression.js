import {declareBadgeRule} from '#/plugin/open-badge/badge-rule'
import {trans} from '#/main/app/intl'
import {createElement} from 'react'
import {Html} from '#/main/app/components/html'
import get from 'lodash/get'
import {route} from '#/main/evaluation/sequence'

export default declareBadgeRule({
  name: 'sequence_progression',
  meta: {
    label: trans('sequence_progression', {}, 'badge')
  },
  render: (rule) => createElement(Html, {
    children: trans('sequence_progression_desc', {
      progression: `<b>${get(rule, 'data.value', '')}%</b>`,
      sequence: `<a class="fw-bolder text-reset" href="#${route(rule.subject)}">`+get(rule, 'subject.name')+'</a>'
    }, 'badge')
  }),
  default: (rule) => Object.assign({}, rule, {
    subjectClass: 'Claroline\\EvaluationBundle\\Entity\\Sequence\\Sequence'
  }),
  configure: (contextType, contextId) => [
    {
      name: 'subject',
      type: 'sequence',
      label: trans('sequence', {}, 'evaluation'),
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
