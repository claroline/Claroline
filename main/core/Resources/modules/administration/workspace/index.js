import {bootstrap} from '#/main/core/utilities/app/bootstrap'

// modals
import {registerModalType} from '#/main/core/layout/modal'
import {ConfirmModal} from '#/main/core/layout/modal/components/confirm.jsx'
import {UserPickerModal} from '#/main/core/layout/modal/components/user-picker.jsx'

// reducers
import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {reducer as paginationReducer} from '#/main/core/layout/pagination/reducer'
import {makeListReducer} from '#/main/core/layout/list/reducer'
import {reducer as workspacesReducer} from '#/main/core/administration/workspace/reducer'

import {Workspaces} from '#/main/core/administration/workspace/components/workspaces.jsx'

// register custom modals for the app
registerModalType('CONFIRM_MODAL', ConfirmModal)
registerModalType('MODAL_USER_PICKER', UserPickerModal)

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspace-administration-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Workspaces,

  // app store configuration
  {
    // app reducers
    workspaces: workspacesReducer,

    // generic reducers
    currentRequests: apiReducer,
    modal: modalReducer,
    list: makeListReducer(),
    pagination: paginationReducer
  },

  // remap data-attributes set on the app DOM container
  (initialData) => {
    return {
      workspaces: {
        data: initialData.workspaces,
        totalResults: initialData.count
      },
      pagination: {
        pageSize: initialData.pageSize,
        current: initialData.page
      },
      list: {
        filters: initialData.filters,
        sortBy: initialData.sortBy ? initialData.sortBy : undefined
      }
    }
  }
)
