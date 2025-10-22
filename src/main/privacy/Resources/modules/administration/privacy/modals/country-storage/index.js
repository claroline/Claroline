/**
 * Country storage modal.
 *
 * Displays a form to modify the country storage of the platform data.
 */

import {registry} from '#/main/app/modals/registry'

// gets the modal component
import {CountryStorageModal} from '#/main/privacy/administration/privacy/modals/country-storage/components/modal'

const MODAL_COUNTRY_STORAGE = 'MODAL_COUNTRY_STORAGE'

// make the modal available for use
registry.add(MODAL_COUNTRY_STORAGE, CountryStorageModal)

export {
  MODAL_COUNTRY_STORAGE
}
