import React from 'react'
import get from 'lodash/get'

import {now, trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const ExportEditorPlaning = (props) => {
  const isScheduled = (data) => get(data, 'scheduler._enable') || get(data, 'scheduler.scheduledDate')

  return (
    <EditorPage
      title={trans('planing', {}, 'scheduler')}
      definition={[
        {
          name: 'planing',
          title: trans('planing', {}, 'scheduler'),
          primary: true,
          fields: [
            {
              name: 'scheduler._enable',
              type: 'boolean',
              label: trans('schedule', {}, 'scheduler'),
              calculated: isScheduled,
              onChange: (enabled) => {
                if (enabled) {
                  props.updateProp('scheduler.executionType', 'once')
                  props.updateProp('scheduler.scheduledDate', now())
                } else {
                  props.updateProp('scheduler', {})
                }
              },
              linked: [
                {
                  name: 'scheduler.executionType',
                  type: 'choice',
                  label: trans('type'),
                  displayed: isScheduled,
                  hideLabel: true,
                  required: true,
                  options: {
                    choices: {
                      once: trans('once', {}, 'scheduler'),
                      recurring: trans('recurring', {}, 'scheduler')
                    }
                  }
                }, {
                  name: 'scheduler.scheduledDate',
                  type: 'date',
                  label: trans('scheduled_date', {}, 'scheduler'),
                  displayed: isScheduled,
                  required: true
                }, {
                  name: 'scheduler.executionInterval',
                  type: 'number',
                  label: trans('interval', {}, 'scheduler'),
                  displayed: (data) => isScheduled(data) && 'recurring' === get(data, 'scheduler.executionType'),
                  required: true,
                  options: {
                    unit: trans('days')
                  }
                }, {
                  name: 'scheduler.endDate',
                  type: 'date',
                  label: trans('end_date'),
                  displayed: (data) => isScheduled(data) && 'recurring' === get(data, 'scheduler.executionType'),
                  required: true
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  ExportEditorPlaning
}
