import React from 'react'
import {connect} from 'react-redux'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

const MainComponent = () =>
  <FormData
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
        title: trans('main'),
        defaultOpened: true,
        fields: [
          {
            name: 'display.name',
            type: 'string',
            label: trans('name'),
            required: false
          }/*
          {
            name: 'display.description',
            type: 'string',
            label: trans('description'),
            required: false,
            options: {
              long: true
            }
          }*/
        ]
      }
    ]}
  />


MainComponent.propTypes = {
}

const Main = connect(
  null,
  () => ({ })
)(MainComponent)

export {
  Main
}
