import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const I18nComponent = (props) => {
  return(<FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('i18n'),
        defaultOpened: true,
        fields: [
          {
            name: 'locales.available',
            type: 'choice',
            label: trans('available_languages'),
            required: false,
            options: {
              choices: props.availablesLocales,
              multiple: true,
              condensed: false,
              inline: false
            }
          },
          {
            name: 'locales.default',
            type: 'choice',
            label: trans('default_language'),
            required: false,
            options: {
              choices: props.availablesLocales,
              multiple: false,
              condensed: false,
              inline: false
            }
          }
        ]
      }
    ]}
  />)
}

I18nComponent.propTypes = {
  availablesLocales: T.object.isRequired
}

const I18n = connect(
  state => ({
    availablesLocales: state.availablesLocales
  }),
  null
)(I18nComponent)

export {
  I18n
}
