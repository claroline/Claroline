
import {hasPermission} from '#/main/app/security/permissions'

import {EventAbout} from '#/plugin/cursus/events/event/containers/about'
import {EventDetails} from '#/plugin/cursus/events/event/containers/details'
import {EventForm} from '#/plugin/cursus/events/event/components/form'

export default {
  name: 'training_event',
  icon: 'fa fa-fw fa-graduation-cap',
  canCreate: (contextType, contextData, contextTools) => {
    if ('workspace' === contextType) {
      // training events creation is only enabled in workspace
      const tool = contextTools.find(tool => 'training_events' === tool.name)
      if (tool) {
        return hasPermission('edit', tool)
      }
    }

    return false
  },
  components: {
    about: EventAbout,
    details: EventDetails,
    form: EventForm
  }
}
