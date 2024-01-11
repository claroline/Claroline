/**
 * Terms of service modal.
 *
 * Displays the platform terms of service.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CountryStorageModal} from '#/main/privacy/modals/country-storage/containers/modal'

const MODAL_COUNTRY_STORAGE = 'MODAL_COUNTRY_STORAGE'

// make the modal available for use
registry.add(MODAL_COUNTRY_STORAGE, CountryStorageModal)

export {
  MODAL_COUNTRY_STORAGE
}
