import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route} from '#/plugin/cursus/routing'
import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'

const QuotaForm = (props) =>
  <FormData
    name={props.name}
    meta={false}
    buttons={true}
    target={(data, isNew) => isNew ?
      ['apiv2_cursus_quota_create'] :
      ['apiv2_cursus_quota_update', {id: data.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'organization',
            type: 'organization',
            label: trans('organization'),
            required: true
          }, {
            name: 'threshold',
            type: 'number',
            label: trans('threshold'),
            required: true
          }
        ]
      }
    ]}
  />

QuotaForm.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,

  // from store
  isNew: T.bool.isRequired,
  quota: T.shape(
    QuotaTypes.propTypes
  ),
  update: T.func.isRequired
}

export {
  QuotaForm
}