import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'
import {createStore} from '#/main/core/utilities/redux'
import {registerModalTypes} from '#/main/core/layout/modal'
import {DeleteConfirmModal} from '#/main/core/layout/modal/components/delete-confirm.jsx'
import {makeRouter} from './router'
import {reducers} from './reducers'
import {VIEW_MANAGEMENT} from './enums'
import {AdminTaskToolLayout} from './components/admin-task-tool-layout.jsx'
import {TaskTypeFormModal} from './components/task-type-form-modal.jsx'
import {MessageDetailsModal} from './components/message-details-modal.jsx'

class AdminTaskTool {
  constructor(isCronConfigured, tasks, total) {
    registerModalTypes([
      ['DELETE_MODAL', DeleteConfirmModal],
      ['MODAL_TASK_TYPE_FORM', TaskTypeFormModal],
      ['MODAL_DETAILS_TASK_MESSAGE', MessageDetailsModal]
    ])
    this.store = createStore(
      reducers,
      {
        isCronConfigured: isCronConfigured,
        tasks: {
          data: tasks,
          total: total
        },
        viewMode: VIEW_MANAGEMENT
      }
    )
    makeRouter(this.store.dispatch.bind(this.store))
  }

  render(element) {
    ReactDOM.render(
      React.createElement(
        Provider,
        {store: this.store},
        React.createElement(AdminTaskToolLayout)
      ),
      element
    )
  }
}

const container = document.querySelector('.admin-task-tool-container')
const isCronConfigured = parseInt(container.dataset.isCronConfigured)
const tasks = JSON.parse(container.dataset.tasks)
const total = parseInt(container.dataset.total)
const tool = new AdminTaskTool(isCronConfigured, tasks, total)

tool.render(container)