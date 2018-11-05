import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/administration/parameters/appearance/store/selectors'

const IconsComponent = (props) =>
{
  return (
    <FormData
      name="parameters"
      target={['apiv2_parameters_update']}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: '/header',
        exact: true
      }}
      sections={[
        {
          icon: 'fa fa-fw fa-edit',
          title: trans('icons'),
          defaultOpened: true,
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
  )}

IconsComponent.propTypes = {
  iconSetChoices: T.object.isRequired
}

const Icons = connect(
  (state) => ({
    iconSetChoices: selectors.iconSetChoices(state)
  }),
  () => ({ })
)(IconsComponent)

export {
  Icons
}
