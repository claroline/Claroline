import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/core/administration/scheduled-task/constants'
import {selectors} from '#/main/core/administration/scheduled-task/store'
import {ScheduledTaskCard} from '#/main/core/administration/scheduled-task/data/components/scheduled-task-card'

const ScheduledTasks = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.tasks'}
    fetch={{
      url: ['apiv2_scheduledtask_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/form/${row.id}`
    })}
    delete={{
      url: ['apiv2_scheduledtask_delete_bulk']
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'type',
        label: trans('task'),
        type: 'choice',
        options: {
          choices: constants.TASK_TYPES
        },
        displayed: true
      }, {
        name: 'scheduledDate',
        type: 'date',
        label: trans('scheduled_date'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'meta.lastExecution',
        type: 'date',
        label: trans('lastExecution'),
        displayed: true,
        options: {
          time: true
        }
      }
    ]}

    card={ScheduledTaskCard}
  />

ScheduledTasks.propTypes = {
  path: T.string.isRequired
}

export {
  ScheduledTasks
}
