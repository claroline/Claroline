import {trans} from '#/main/core/translation'

import {StorageGroup} from '#/main/core/layout/form/components/group/storage-group'

const STORAGE_TYPE = 'storage'

// TODO finish implementation

/**
 * Storage definition.
 * Manages storage size values.
 */
const storageDefinition = {
  meta: {
    type: STORAGE_TYPE,
    creatable: false,
    icon: 'fa fa-fw fa fa-database',
    label: trans('storage'),
    description: trans('storage_desc')
  },

  /**
   * Custom components for numbers rendering.
   */
  components: {
    form: StorageGroup
  }
}

export {
  STORAGE_TYPE,
  storageDefinition
}
