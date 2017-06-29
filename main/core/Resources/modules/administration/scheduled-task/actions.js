import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'
import {REQUEST_SEND} from '#/main/core/api/actions'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {actions as paginationActions} from '#/main/core/layout/pagination/actions'
import {select as paginationSelect} from '#/main/core/layout/pagination/selectors'
import {
  VIEW_MANAGEMENT,
  VIEW_MAIL_FORM,
  VIEW_MESSAGE_FORM
} from './enums'

export const UPDATE_VIEW_MODE = 'UPDATE_VIEW_MODE'
export const TASKS_LOAD = 'TASKS_LOAD'
export const TASK_ADD = 'TASK_ADD'
export const TASK_FORM_RESET = 'TASK_FORM_RESET'
export const TASK_FORM_LOAD = 'TASK_FORM_LOAD'
export const TASK_FORM_TYPE_UPDATE = 'TASK_FORM_TYPE_UPDATE'

export const actions = {}

actions.loadTasks = makeActionCreator(TASKS_LOAD, 'tasks', 'total')

actions.fetchTasks = () => (dispatch, getState) => {
  const state = getState()
  const page = paginationSelect.current(state)
  const pageSize = paginationSelect.pageSize(state)
  const url = generateUrl('claro_admin_scheduled_tasks_search', {page: page, limit: pageSize}) + '?'

  // build queryString
  let queryString = ''

  // add filters
  const filters = listSelect.filters(state)
  if (0 < filters.length) {
    queryString += filters.map(filter => `filters[${filter.property}]=${filter.value}`).join('&')
  }

  // add sort by
  const sortBy = listSelect.sortBy(state)
  if (sortBy.property && 0 !== sortBy.direction) {
    queryString += `${0 < queryString.length ? '&':''}sortBy=${-1 === sortBy.direction ? '-':''}${sortBy.property}`
  }

  dispatch({
    [REQUEST_SEND]: {
      url: url + queryString,
      request: {
        method: 'GET'
      },
      success: (data, dispatch) => {
        dispatch(listActions.resetSelect())
        dispatch(actions.loadTasks(JSON.parse(data.tasks), data.total))
      }
    }
  })
}

actions.updateViewMode = makeActionCreator(UPDATE_VIEW_MODE, 'mode')

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

actions.deleteTasks = (tasks) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_admin_scheduled_tasks_delete') + getQueryString(tasks),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(paginationActions.changePage(0))
      dispatch(actions.fetchTasks())
    }
  }
})

actions.addTask = makeActionCreator(TASK_ADD, 'task')

actions.resetTaskForm = makeActionCreator(TASK_FORM_RESET)

actions.loadTaskForm = makeActionCreator(TASK_FORM_LOAD, 'task')

actions.updateTaskFormType = makeActionCreator(TASK_FORM_TYPE_UPDATE, 'value')

const getQueryString = (idsList) => '?' + idsList.map(id => 'ids[]='+id).join('&')