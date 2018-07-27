import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {registry} from '#/main/app/modals/registry'
import {trans} from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form.jsx'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

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
                label: trans('document'),
                displayed: null !== this.state.type && constants.DOCUMENT_TYPE_RESOURCE !== this.state.type,
                required: true,
                options: {
                  autoUpload: false
                }
              }
            ]
          }
        ]}
      >
        { null !== this.state.type && constants.DOCUMENT_TYPE_RESOURCE === this.state.type &&
          <EmptyPlaceholder
            size="lg"
            icon="fa fa-folder"
            title={''}
          >
            <button
              type="button"
              className="btn btn-primary btn-emphasis"
              onClick={() => this.props.pickResource(this.state)}
            >
              <span className="fa fa-fw fa-plus icon-with-text-right"/>
              {trans('add_resource')}
            </button>
          </EmptyPlaceholder>
        }
      </DataFormModal>
    )
  }
}

AddDocumentModal.propTypes = {
  save: T.func.isRequired,
  pickResource: T.func.isRequired,
  allowedDocuments: T.arrayOf(
    T.oneOf(Object.keys(constants.DOCUMENT_TYPES))
  ).isRequired
}

registry.add(MODAL_ADD_DOCUMENT, AddDocumentModal)

export {
  MODAL_ADD_DOCUMENT,
  AddDocumentModal
}
