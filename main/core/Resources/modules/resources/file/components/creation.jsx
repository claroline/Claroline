import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {selectors} from '#/main/core/resource/modals/creation/store'

const FileForm = props =>
  <FormContainer
    level={5}
    name={selectors.FORM_NAME}
    dataPart="resource"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'file',
            label: trans('file'),
            type: 'file',
            required: true,
            onChange: (file) => props.update(file),
            options: {
              unzippable: true
            }
          }
        ]
      }
    ]}
  />

FileForm.propTypes = {
  update: T.func.isRequired
}

const FileCreation = connect(
  null,
  () => ({
    update() {
      // set resource name

      // set resource mime type
    }
  })
)(FileForm)

export {
  FileCreation
}
