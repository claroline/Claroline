import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/main/core/administration/parameters/appearance/store/selectors'

const IconsComponent = (props) =>
  <FormData
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-edit',
        title: trans('icons'),
        fields: [
          {
            name: 'display.resource_icon_set',
            type: 'choice',
            label: trans('icons'),
            required: false,
            options: {
              multiple: false,
              condensed: true,
              choices: props.iconSetChoices
            }
          }
        ]
      }
    ]}
  />

IconsComponent.propTypes = {
  iconSetChoices: T.object.isRequired
}

const Icons = connect(
  (state) => ({
    iconSetChoices: selectors.iconSetChoices(state)
  })
)(IconsComponent)

export {
  Icons
}
