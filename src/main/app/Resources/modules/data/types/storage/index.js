import {trans} from '#/main/app/intl/translation'

import {StorageInput} from '#/main/app/data/types/storage/components/input'

// TODO finish implementation

/**
 * Storage definition.
 * Manages storage size values.
 */
const dataType = {
  name: 'storage',
  meta: {
    icon: 'fa fa-fw fa fa-database',
    label: trans('storage', {}, 'data'),
    description: trans('storage_desc', {}, 'data')
  },

  /**
   * Custom components for storage size rendering.
   */
  components: {
    input: StorageInput
  }
}

export {
  dataType
}
