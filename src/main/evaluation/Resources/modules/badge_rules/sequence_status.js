import {trans} from '#/main/app/intl'
import {declareBadgeRule} from '#/plugin/open-badge/badge-rule'

import {constants} from '#/main/evaluation/constants'
import {createElement} from 'react'
import {Html} from '#/main/app/components/html'
import get from 'lodash/get'
import {route} from '#/main/evaluation/sequence'

export default declareBadgeRule({
  name: 'sequence_status',
  meta: {
    label: trans('sequence_status', {}, 'badge')
  },
  render: (rule) => createElement(Html, {
    children: trans('sequence_status_desc', {
      status: `<b>${constants.EVALUATION_STATUSES_SHORT[get(rule, 'data.value')]}</b>`,
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
      type: 'choice',
      label: trans('status'),
      required: true,
      options: {
        choices: Object.keys(constants.EVALUATION_STATUSES_SHORT)
          .filter(status => ![constants.EVALUATION_STATUS_UNKNOWN, constants.EVALUATION_STATUS_NOT_ATTEMPTED].includes(status))
          .reduce((acc, current) => Object.assign(acc, {
            [current]: constants.EVALUATION_STATUSES_SHORT[current]
          }), {})
      }
    }
  ]
})
