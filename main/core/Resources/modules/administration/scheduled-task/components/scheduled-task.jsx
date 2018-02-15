import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {constants} from '#/main/core/administration/scheduled-task/constants'
import {actions} from '#/main/core/administration/scheduled-task/actions'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'

const ScheduledTaskForm = props => {
  return(
    <FormContainer
      level={2}
      name="task"
      sections={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'scheduledDate',
              type: 'date',
              label: trans('planning_date'),
              required: true,
              options: {
                time: true
              }
            }, {
              name: 'type',
              type: 'enum',
              label: trans('task'),
              required: true,
              disabled: !props.new,
              options: {
                choices: constants.TASK_TYPES
              }
            }
          ]
        }, {
          id: 'message',
          icon: 'fa fa-fw fa-envelope-o',
          title: trans('message'),
          fields: [
            {
              name: 'data.object',
              type: 'string',
              label: trans('message_form_object')
            }, {
              name: 'data.content',
              type: 'html',
              label: trans('message_form_content'),
              required: true,
              options: {
                minRows: 5
              }
            }
          ]
        }
      ]}
    >
    {
      <FormSections
        level={3}
      >
        <FormSection
          id="task-users"
          icon="fa fa-fw fa-user"
          title={trans('users')}
          disabled={props.new}
          actions={[
            {
              icon: 'fa fa-fw fa-plus',
              label: trans('add_users'),
              action: () => props.pickUsers(props.task.id)
            }
          ]}
        >
          <DataListContainer
            name="task.users"
            open={UserList.open}
            fetch={{
              url: ['apiv2_scheduledtask_list_users', {id: props.task.id}],
              autoload: props.task.id && !props.new
            }}
            delete={{
              url: ['apiv2_scheduledtask_remove_users', {id: props.task.id}]
            }}
            definition={UserList.definition}
            card={UserList.card}
          />
        </FormSection>
      </FormSections>
      }
    </FormContainer>
  )

}

ScheduledTaskForm.propTypes = {
  new: T.bool.isRequired,
  task: T.shape({
    id: T.string
  }).isRequired,
  pickUsers: T.func.isRequired
}

const ScheduledTask = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'task')),
    task: formSelect.data(formSelect.form(state, 'task'))
  }),
  dispatch =>({
    pickUsers(taskId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_users'),
        confirmText: trans('add'),
        name: 'picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(taskId, selected))
      }))
    }
  })
)(ScheduledTaskForm)

export {
  ScheduledTask
}
