import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const DropboxParameters = props =>
  <FormData
    name={props.name+'.dropbox'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

DropboxParameters.propTypes = {
  name: T.string.isRequired
}

export {
  DropboxParameters
}