import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const FacebookParameters = props =>
  <FormData
    name={props.name+'.facebook'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

FacebookParameters.propTypes = {
  name: T.string.isRequired
}

export {
  FacebookParameters
}