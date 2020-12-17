import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const GitHubParameters = props =>
  <FormData
    name={props.name+'.github'}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }
    ]}
  />

GitHubParameters.propTypes = {
  name: T.string.isRequired
}

export {
  GitHubParameters
}