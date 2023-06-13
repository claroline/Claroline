import { registry } from '#/main/app/modals/registry'
import { CountryModal } from '#/main/privacy/administration/privacy/modals/country/containers/modal'

const MODAL_COUNTRY_STORAGE = 'MODAL_COUNTRY_STORAGE'

registry.add(MODAL_COUNTRY_STORAGE, CountryModal)

export {
  MODAL_COUNTRY_STORAGE
}