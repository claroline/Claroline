import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const GoogleParameters = props =>
  <FormData
    name={props.name+'.google'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

GoogleParameters.propTypes = {
  name: T.string.isRequired
}

export {
  GoogleParameters
}