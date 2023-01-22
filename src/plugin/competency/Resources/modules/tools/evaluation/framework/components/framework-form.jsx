import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'

const FrameworkForm = (props) =>
  <FormData
    level={3}
    name={competencySelectors.STORE_NAME + '.frameworks.form'}
    buttons={true}
    target={(competency, isNew) => isNew ?
      ['apiv2_competency_create'] :
      ['apiv2_competency_update', {id: competency.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/competencies/frameworks`,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }, {
            name: 'scale',
            type: 'competency_scale',
            label: trans('scale', {}, 'competency'),
            required: true
          }
        ]
      }
    ]}
  />

FrameworkForm.propTypes = {
  path: T.string.isRequired
}

export {
  FrameworkForm
}
