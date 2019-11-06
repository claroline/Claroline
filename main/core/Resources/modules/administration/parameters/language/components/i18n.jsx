import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route as adminRoute} from '#/main/core/administration/routing'
import {selectors} from '#/main/core/administration/parameters/store'

const I18n = (props) =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: adminRoute('main_settings'),
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'locales.available',
            type: 'locale',
            label: trans('available_languages'),
            required: true,
            options: {
              available: props.availableLocales,
              multiple: true
            }
          }, {
            name: 'locales.default',
            type: 'locale',
            label: trans('default_language'),
            required: true,
            options: {
              available: props.availableLocales
            }
          }
        ]
      }
    ]}
  />

I18n.propTypes = {
  availableLocales: T.arrayOf(T.string).isRequired,
  locales: T.shape({
    available: T.arrayOf(T.string),
    default: T.string
  })
}

export {
  I18n
}
