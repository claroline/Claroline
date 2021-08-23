import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormDataModal} from '#/main/app/modals/form/components/data'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const AddDocumentModal = (props) =>
  <FormDataModal
    {...omit(props, 'type')}
    icon="fa fa-fw fa-plus"
    title={trans('new_document', {}, 'dropzone')}

    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'type',
            type: 'type',
            label: trans('type'),
            hideLabel: true,
            calculated: () => ({
              icon: <span className={constants.DOCUMENT_TYPE_ICONS[props.type]} />,
              name: constants.DOCUMENT_TYPES[props.type],
              description: trans(`document_${props.type}_desc`, {}, 'dropzone')
            })
          }, {
            name: 'data',
            type: props.type,
            label: trans('document', {}, 'dropzone'),
            required: true,
            options: {
              autoUpload: false, // for file
              minRows: 10 // for html
            }
          }
        ]
      }
    ]}
  />

AddDocumentModal.propTypes = {
  type: T.string.isRequired,
  save: T.func.isRequired
}

export {
  AddDocumentModal
}
