import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const LinkedinParameters = props =>
  <FormData
    name={props.name+'.linkedin'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

LinkedinParameters.propTypes = {
  name: T.string.isRequired
}

export {
  LinkedinParameters
}