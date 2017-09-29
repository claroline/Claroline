import React from 'react'
import {PropTypes as T} from 'prop-types'
import {withRouter} from 'react-router-dom'
import {connect} from 'react-redux'
import {NavLink} from 'react-router-dom'

import {t, trans, transChoice} from '#/main/core/translation'
import {localeDate} from '#/main/core/layout/data/types/date/utils'

import {
  PageContainer as Page,
  PageHeader,
  PageContent,
  PageActions,
  PageAction
} from '#/main/core/layout/page'
import {MODAL_DELETE_CONFIRM, MODAL_GENERIC_TYPE_PICKER} from '#/main/core/layout/modal'
import {DataListContainer as DataList} from '#/main/core/layout/list/containers/data-list.jsx'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions} from '#/main/core/administration/scheduled-task/actions'
import {select} from '#/main/core/administration/scheduled-task/selectors'
import {constants} from '#/main/core/administration/scheduled-task/constants'

/*showTaskDetails(task) {
  const type = task.type === 'mail' ? 'MESSAGE' : task.type.toUpperCase()

  this.setState({
    modal: {
      type: `MODAL_DETAILS_TASK_${type}`,
      urlModal: null,
      props: {
        title: task.name ? task.name : t(task.type),
        task: task
      },
      fading: false
    }
  })
}*/

const ScheduledTasksPage = props =>
  <Page id="scheduled-task-management">
    <PageHeader
      title={trans('tasks_scheduling', {}, 'tools')}
    >
      <PageActions>
        <PageAction
          id="scheduled-task-add"
          title={t('add_a_task')}
          icon="fa fa-plus"
          primary={true}
          disabled={!props.isCronConfigured}
          action={props.createTask}
        />
      </PageActions>
    </PageHeader>

    <PageContent>
      <DataList
        name="tasks"
        definition={[
          {
            name: 'name',
            label: t('name'),
            displayed: true,
            renderer: (rowData) => {
              const link = <NavLink to={`/${rowData.id}`}>{rowData.name}</NavLink>

              return link
            }
          }, {
            name: 'type',
            label: t('type'),
            renderer: (rowData) => t(rowData.type),
            displayed: true
          }, {
            name: 'scheduledDate',
            type: 'date',
            label: t('scheduled_date'),
            displayed: true
          }, {
            name: 'meta.lastExecution',
            type: 'date',
            label: t('lastExecution'),
            displayed: true
          }
        ]}

        actions={[
          {
            icon: 'fa fa-fw fa-pencil',
            label: t('edit'),
            action: (rows) => props.editTask(rows[0]),
            context: 'row'
          }, {
            icon: 'fa fa-fw fa-trash-o',
            label: t('delete'),
            action: (rows) => props.removeTasks(rows),
            isDangerous: true
          }
        ]}

        card={(row) => ({
          icon: 'fa fa-clock',
          title: row.name,
          subtitle: row.type,
          footer:
            row.meta.lastExecution &&
            <span>
              executed at <b>{localeDate(row.meta.lastExecution)}</b>
            </span>
        })}
      />
    </PageContent>
  </Page>

ScheduledTasksPage.propTypes = {
  isCronConfigured: T.bool.isRequired,
  createTask: T.func.isRequired,
  removeTasks: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    isCronConfigured: select.isCronConfigured(state)
  }
}

function mapDispatchToProps(dispatch, ownProps) {
  return {
    createTask() {
      dispatch(
        modalActions.showModal(MODAL_GENERIC_TYPE_PICKER, {
          title: t('task_type_selection_title'),
          types: constants.taskTypes,
          handleSelect: (type) => ownProps.history.push(type)
        })
      )
    },

    editTask() {

    },

    removeTasks(tasks) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: transChoice('remove_scheduled_tasks', tasks.length, {count: tasks.length}, 'platform'),
          question: t('remove_scheduled_tasks_confirm', {
            task_list: tasks.map(task => task.name).join(', ')
          }),
          handleConfirm: () => dispatch(actions.removeTasks(tasks))
        })
      )
    }
  }
}

const ScheduledTasks = withRouter(connect(mapStateToProps, mapDispatchToProps)(ScheduledTasksPage))

export {
  ScheduledTasks
}
