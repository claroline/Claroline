import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const PdfComponent = () =>
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
        title: trans('PDF'),
        defaultOpened: true,
        fields: [
          {
            name: 'pdf.active',
            type: 'boolean',
            label: trans('activated'),
            required: true
          }
        ]
      }
    ]}
  />


PdfComponent.propTypes = {
}

const Pdf = connect(
  null,
  () => ({ })
)(PdfComponent)

export {
  Pdf
}
