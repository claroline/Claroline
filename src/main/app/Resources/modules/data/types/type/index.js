
import {TypeDisplay} from '#/main/app/data/types/type/components/display'

/**
 * Displays meta (eg. icon, name, description) about the type of an object.
 * Used by ResourceNodes, WidgetContents, DataSources, Events.
 *
 * Note. This may be managed by the choice type later as we plan to add icon and description for choices.
 */
const dataType = {
  name: 'type',
  meta: {
    creatable: false
  },
  components: {
    input: TypeDisplay, // no input, the common use case is to use a grid selection
    display: TypeDisplay
  }
}

export {
  dataType
}
