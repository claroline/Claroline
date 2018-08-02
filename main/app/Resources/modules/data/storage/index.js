import {trans} from '#/main/core/translation'

import {StorageGroup} from '#/main/core/layout/form/components/group/storage-group'

// TODO finish implementation

/**
 * Storage definition.
 * Manages storage size values.
 */
const dataType = {
  name: 'storage',
  meta: {
    icon: 'fa fa-fw fa fa-database',
    label: trans('storage'),
    description: trans('storage_desc')
  },

  /**
   * Custom components for storage size rendering.
   */
  components: {
    form: StorageGroup
  }
}

export {
  dataType
}
