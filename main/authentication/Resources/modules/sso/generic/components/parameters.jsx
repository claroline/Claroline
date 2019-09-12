import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const GenericParameters = props =>
  <FormData
    name={props.name+'.generic'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

GenericParameters.propTypes = {
  name: T.string.isRequired
}

export {
  GenericParameters
}