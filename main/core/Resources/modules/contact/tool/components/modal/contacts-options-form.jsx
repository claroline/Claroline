import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form.jsx'

const MODAL_CONTACTS_OPTIONS_FORM = 'MODAL_CONTACTS_OPTIONS_FORM'

class ContactsOptionsFormModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      options: props.data ? props.data : {}
    }
  }

  render() {
    return (
      <DataFormModal
        {...this.props}
        save={(data) => {this.props.save(data)}}
        icon="fa fa-fw fa-cog"
        title={t('configuration')}
        sections={[
          {
            id: 'general',
            title: t('general'),
            primary: true,
            fields: [
              {
                name: 'data.show_username',
                type: 'boolean',
                label: t('show_username')
              }, {
                name: 'data.show_mail',
                type: 'boolean',
                label: t('show_mail')
              }, {
                name: 'data.show_phone',
                type: 'boolean',
                label: t('show_phone')
              }
            ]
          }
        ]}
      />
    )
  }
}

ContactsOptionsFormModal.propTypes = {
  data: T.object.isRequired,
  save: T.func.isRequired
}

export {
  MODAL_CONTACTS_OPTIONS_FORM,
  ContactsOptionsFormModal
}
