import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'

import {actions as listActions} from '#/main/core/layout/list/actions'
import {getDataQueryString} from '#/main/core/layout/list/utils'

import {REQUEST_SEND} from '#/main/core/api/actions'
import {
  VIEW_MANAGEMENT,
  VIEW_MAIL_FORM,
  VIEW_MESSAGE_FORM
} from './constants'

export const UPDATE_VIEW_MODE = 'UPDATE_VIEW_MODE'
/*export const TASKS_LOAD = 'TASKS_LOAD'*/
export const TASK_ADD = 'TASK_ADD'
export const TASK_FORM_RESET = 'TASK_FORM_RESET'
export const TASK_FORM_LOAD = 'TASK_FORM_LOAD'
export const TASK_FORM_TYPE_UPDATE = 'TASK_FORM_TYPE_UPDATE'

export const actions = {}

actions.updateViewMode = makeActionCreator(UPDATE_VIEW_MODE, 'mode')
actions.addTask = makeActionCreator(TASK_ADD, 'task')
actions.resetTaskForm = makeActionCreator(TASK_FORM_RESET)
actions.loadTaskForm = makeActionCreator(TASK_FORM_LOAD, 'task')
actions.updateTaskFormType = makeActionCreator(TASK_FORM_TYPE_UPDATE, 'value')

actions.editTask = makeActionCreator(UPDATE_VIEW_MODE, 'mode')

actions.displayManagementView = () => {
  return (dispatch) => {
    dispatch(actions.resetTaskForm('mail'))
    dispatch(actions.updateViewMode(VIEW_MANAGEMENT))
  }
}

actions.displayMailView = () => {
  return (dispatch) => {
    dispatch(actions.updateTaskFormType('mail'))
    dispatch(actions.updateViewMode(VIEW_MAIL_FORM))
  }
}

actions.displayMessageView = () => {
  return (dispatch) => {
    dispatch(actions.updateTaskFormType('message'))
    dispatch(actions.updateViewMode(VIEW_MESSAGE_FORM))
  }
}

actions.createMessageTask = (data) => {
  return (dispatch) => {
    const formData = new FormData()
    formData.append('type', data['type'])
    formData.append('scheduledDate', data['scheduledDate'])
    formData.append('users', data['receiversIds'])
    const mailData = {
      object: data['object'],
      content: data['content']
    }
    formData.append('data', JSON.stringify(mailData))

    if (data['name'] !== undefined) {
      formData.append('name', data['name'])
    }

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_admin_scheduled_task_create'),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.addTask(JSON.parse(data)))
        }
      }
    })
  }
}

actions.editMessageTask = (taskId, data) => {
  return (dispatch) => {
    const formData = new FormData()
    formData.append('scheduledDate', data['scheduledDate'])
    formData.append('users', data['receiversIds'])
    const mailData = {
      object: data['object'],
      content: data['content']
    }
    formData.append('data', JSON.stringify(mailData))

    if (data['name'] !== undefined) {
      formData.append('name', data['name'])
    }

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_admin_scheduled_task_edit', {task: taskId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.fetchTasks())
        }
      }
    })
  }
}

actions.removeTasks = (tasks) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_admin_scheduled_tasks_delete') + getDataQueryString(tasks),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(listActions.changePage(0))
      dispatch(listActions.fetchData('tasks'))
    }
  }
})
