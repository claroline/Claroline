import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/resource/modals/creation/store/selectors'

const FileCreation = props =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_RESOURCE_PART}
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
            onChange: (file) => props.update(props.newNode, file)
          }
        ]
      }
    ]}
  />

FileCreation.propTypes = {
  newNode: T.shape({
    name: T.string
  }),
  update: T.func.isRequired
}

export {
  FileCreation
}
