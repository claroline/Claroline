import React from 'react'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'

const DirectoryEditor = () =>
  <FormContainer
    name="directoryForm"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

DirectoryEditor.propTypes = {

}

export {
  DirectoryEditor
}
