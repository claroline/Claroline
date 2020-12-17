import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {registry} from '#/main/app/modals/registry'
import {trans} from '#/main/app/intl/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

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
      <FormDataModal
        {...omit(this.props, 'allowedDocuments', 'pickResource')}
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
                type: 'choice',
                label: trans('type'),
                required: true,
                onChange: (documentType) => this.setState({type: documentType}),
                options: {
                  noEmpty: false,
                  condensed: true,
                  choices: this.state.allowedTypes
                }
              }, {
                name: 'data',
                type: this.state.type,
                label: trans('document', {}, 'dropzone'),
                required: true,
                displayed: !!this.state.type,
                options: {
                  autoUpload: false // for file
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

registry.add(MODAL_ADD_DOCUMENT, AddDocumentModal)

export {
  MODAL_ADD_DOCUMENT,
  AddDocumentModal
}
