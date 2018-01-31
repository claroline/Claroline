import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {registerModals} from '#/main/core/layout/modal'

import {reducer} from '#/main/core/contact/tool/reducer'
import {Tool} from '#/main/core/contact/tool/components/tool.jsx'
import {
  MODAL_CONTACTS_OPTIONS_FORM,
  ContactsOptionsFormModal
} from '#/main/core/contact/tool/components/modal/contacts-options-form.jsx'

registerModals([
  [MODAL_CONTACTS_OPTIONS_FORM, ContactsOptionsFormModal]
])

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.contacts-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Tool,

  // app store configuration
  reducer
)
