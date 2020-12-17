import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const Office365Parameters = props =>
  <FormData
    name={props.name+'.office_365'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

Office365Parameters.propTypes = {
  name: T.string.isRequired
}

export {
  Office365Parameters
}