
import {hasPermission} from '#/main/app/security/permissions'

import {EventAbout} from '#/plugin/agenda/events/event/containers/about'
import {EventDetails} from '#/plugin/agenda/events/event/containers/details'
import {EventForm} from '#/plugin/agenda/events/event/components/form'

export default {
  name: 'event',
  icon: 'fa fa-fw fa-calendar',
  canCreate: (contextType, contextData, contextTools) => {
    const agendaTool = contextTools.find(tool => 'agenda' === tool.name)
    if (agendaTool) {
      return hasPermission('edit', agendaTool)
    }

    return false
  },
  components: {
    about: EventAbout,
    details: EventDetails,
    form: EventForm
  }
}
