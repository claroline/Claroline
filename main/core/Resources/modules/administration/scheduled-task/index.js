import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {routedApp} from '#/main/core/router'

import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {reducer} from '#/main/core/administration/scheduled-task/reducer'
import {actions} from '#/main/core/administration/scheduled-task/actions'

import {ScheduledTasks} from '#/main/core/administration/scheduled-task/components/scheduled-tasks.jsx'
import {ScheduledTask} from '#/main/core/administration/scheduled-task/components/scheduled-task.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.scheduled-tasks-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  routedApp([
    {
      path: '/',
      component: ScheduledTasks,
      exact: true
    }, {
      path: '/:id',
      component: ScheduledTask,
      onEnter: (nextState) => actions.editTask(nextState.params.id),
      onLeave: () => actions.resetTaskForm()
    }
  ]),

  // app store configuration
  {
    // app reducers
    isCronConfigured: (state = false) => state,
    tasks: reducer.tasks,
    taskForm: reducer.taskForm,

    // generic reducers
    currentRequests: apiReducer,
    modal: modalReducer
  },

  // remap data-attributes set on the app DOM container
  (initialData) => ({
    isCronConfigured: initialData.isCronConfigured,
    tasks: initialData.tasks
  })
)
