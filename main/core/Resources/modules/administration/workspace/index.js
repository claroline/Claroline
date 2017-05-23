import React from 'react'
import ReactDOM from 'react-dom'
import {Provider} from 'react-redux'

import {createStore} from '#/main/core/utilities/redux'
import {registerModalType} from '#/main/core/layout/modal'
import {ConfirmModal} from '#/main/core/layout/modal/components/confirm.jsx'
import {UserPickerModal} from '#/main/core/layout/modal/components/user-picker.jsx'

import {reducer} from '#/main/core/administration/workspace/reducer'
import {Workspaces} from '#/main/core/administration/workspace/components/workspaces.jsx'

class WorkspaceAdministration {
  constructor(initialData) {
    registerModalType('CONFIRM_MODAL', ConfirmModal)
    registerModalType('MODAL_USER_PICKER', UserPickerModal)

    this.store = createStore(reducer, initialData)
  }

  render(element) {
    ReactDOM.render(
      React.createElement(
        Provider,
        {store: this.store},
        React.createElement(Workspaces)
      ),
      element
    )
  }
}

const container = document.querySelector('.workspace-administration-container')
const workspaces = JSON.parse(container.dataset.workspaces)
const count = parseInt(container.dataset.count)
const page = parseInt(container.dataset.page)
const pageSize = parseInt(container.dataset.pagesize)
const filters = JSON.parse(container.dataset.filters)
const sortBy = JSON.parse(container.dataset.orderby)

const adminTool = new WorkspaceAdministration({
  workspaces: {
    data: workspaces,
    totalResults: count
  },
  pagination: {
    pageSize,
    current: page
  },
  list: {
    filters,
    sortBy
  }
})

adminTool.render(container)
