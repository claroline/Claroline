import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'

const Scale = (props) =>
  <FormData
    level={3}
    name={competencySelectors.STORE_NAME + '.scales.current'}
    buttons={true}
    target={(scale, isNew) => isNew ?
      ['apiv2_competency_scale_create'] :
      ['apiv2_competency_scale_update', {id: scale.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/competencies/scales`,
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
            name: 'levels',
            type: 'enum',
            label: trans('levels', {}, 'competency'),
            options: {
              placeholder: trans('level.none', {}, 'competency'),
              addButtonLabel: trans('level.add', {}, 'competency'),
              unique: true
            },
            required: true
          }
        ]
      }
    ]}
  />

Scale.propTypes = {
  path: T.string.isRequired
}

export {
  Scale
}
