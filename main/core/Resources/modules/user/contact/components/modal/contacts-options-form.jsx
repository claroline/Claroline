import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

const MODAL_CONTACTS_OPTIONS_FORM = 'MODAL_CONTACTS_OPTIONS_FORM'

const ContactsOptionsFormModal = props =>
  <FormDataModal
    {...props}
    icon="fa fa-fw fa-cog"
    title={trans('configuration')}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data.show_username',
            type: 'boolean',
            label: trans('show_username')
          }, {
            name: 'data.show_mail',
            type: 'boolean',
            label: trans('show_mail')
          }, {
            name: 'data.show_phone',
            type: 'boolean',
            label: trans('show_phone')
          }
        ]
      }
    ]}
  />

ContactsOptionsFormModal.propTypes = {
  data: T.object.isRequired,
  save: T.func.isRequired
}

export {
  MODAL_CONTACTS_OPTIONS_FORM,
  ContactsOptionsFormModal
}
