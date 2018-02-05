import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form.jsx'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const MODAL_ADD_DOCUMENT = 'MODAL_ADD_DOCUMENT'

class AddDocumentModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      type: null,
      allowedTypes: this.props.allowedDocuments.reduce((acc, current) => {
        acc[current] = constants.DOCUMENT_TYPES[current]

        return acc
      }, {})
    }
  }

  render() {
    return (
      <DataFormModal
        {...this.props}
        icon="fa fa-fw fa-plus"
        title={trans('add_document', {}, 'dropzone')}
        saveButtonText={trans('add')}
        sections={[
          {
            id: 'general',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'type',
                type: 'enum',
                label: trans('type'),
                required: true,
                onChange: (documentType) => this.setState({type: documentType}),
                options: {
                  noEmpty: false,
                  choices: this.state.allowedTypes
                }
              }, {
                name: 'data',
                type: this.state.type,
                label: trans('document'),
                displayed: null !== this.state.type,
                required: true,
                options: {
                  autoUpload: false
                }
              }
            ]
          }
        ]}
      />
    )
  }
}

AddDocumentModal.propTypes = {
  save: T.func.isRequired,
  allowedDocuments: T.arrayOf(
    T.oneOf(Object.keys(constants.DOCUMENT_TYPES))
  ).isRequired
}

export {
  MODAL_ADD_DOCUMENT,
  AddDocumentModal
}
