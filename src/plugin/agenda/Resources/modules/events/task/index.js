
import {hasPermission} from '#/main/app/security/permissions'

import {TaskAbout} from '#/plugin/agenda/events/task/containers/about'
import {TaskDetails} from '#/plugin/agenda/events/task/containers/details'
import {TaskForm} from '#/plugin/agenda/events/task/components/form'

export default {
  name: 'task',
  icon: 'fa fa-fw fa-tasks',
  canCreate: (contextType, contextData, contextTools) => {
    const agendaTool = contextTools.find(tool => 'agenda' === tool.name)
    if (agendaTool) {
      return hasPermission('edit', agendaTool)
    }

    return false
  },
  components: {
    about: TaskAbout,
    details: TaskDetails,
    form: TaskForm
  }
}
