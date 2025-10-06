import {createElement} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {declareBadgeRule} from '#/plugin/open-badge/badge-rule'

import {route} from '#/main/core/resource'

export default declareBadgeRule({
  name: 'resource_score',
  meta: {
    label: trans('resource_score', {}, 'badge')
  },
  render: (rule) => createElement(Html, {
    children: trans(`resource_score_${get(rule, 'data.comparator', 'gte')}_desc`, {
      min: `<b>${get(rule, 'data.min', '')}%</b>`,
      max: `<b>${get(rule, 'data.max', '')}%</b>`,
      score: `<b>${get(rule, 'data.value', '')}%</b>`,
      resource: `<a class="fw-bolder text-reset" href="#${route(rule.subject)}">`+get(rule, 'subject.name')+'</a>'
    }, 'badge')
  }),
  default: (rule) => Object.assign({}, rule, {
    data: {comparator: 'gte'},
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
      name: 'data.comparator',
      type: 'choice',
      label: trans('comparator'),
      required: true,
      hideLabel: true,
      options: {
        choices: {
          gte: trans('Supérieur ou égal à'),
          lte: trans('Inférieur ou égal à'),
          equal: trans('Égal à'),
          between: trans('Compris entre')
        }
      }
    }, {
      name: 'data.value',
      type: 'number',
      label: trans('score', {}, 'evaluation'),
      displayed: (formData) => 'between' !== get(formData, 'data.comparator'),
      required: true,
      options: {
        min: 0,
        max: 100,
        unit: '%'
      }
    }, {
      name: 'data.min',
      type: 'number',
      label: trans('score_min', {}, 'evaluation'),
      displayed: (formData) => 'between' === get(formData, 'data.comparator'),
      required: true,
      options: {
        min: 0,
        max: 100,
        unit: '%'
      }
    }, {
      name: 'data.max',
      type: 'number',
      label: trans('score_max', {}, 'evaluation'),
      displayed: (formData) => 'between' === get(formData, 'data.comparator'),
      required: true,
      options: {
        min: 0,
        max: 100,
        unit: '%'
      }
    }
  ]
})
