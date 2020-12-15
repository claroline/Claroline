import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const TwitterParameters = props =>
  <FormData
    name={props.name+'.twitter'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

TwitterParameters.propTypes = {
  name: T.string.isRequired
}

export {
  TwitterParameters
}
