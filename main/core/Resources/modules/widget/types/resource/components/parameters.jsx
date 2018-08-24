import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {actions as formActions} from '#/main/app/content/form/store'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/data/types/resource/prop-types'

import {MODAL_WIDGET_CONTENT} from '#/main/core/widget/content/modals/creation'

const ResourceWidgetForm = (props) =>
  <FormData
    {...props}
    embedded={true}
    level={5}
    name={props.name}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.resource',
            label: trans('resource'),
            type: 'resource',
            required: true,
            onChange: (selected) => {
              props.updateProp(props.name, 'parameters.resource', selected)
              props.showContentParametersModal(props.resource)
            }
          }
        ]
      }
    ]}
  />

ResourceWidgetForm.propTypes = {
  name: T.string.isRequired,
  resource: T.shape(ResourceNodeTypes.propTypes),
  updateProp: T.func.isRequired,
  showContentParametersModal: T.func.isRequired
}

const ResourceWidgetParameters = connect(
  null,
  (dispatch) => ({
    updateProp(formName, prop, value) {
      dispatch(formActions.updateProp(formName, prop, value))
    },
    showContentParametersModal(resource) {
      dispatch(modalActions.showModal(MODAL_WIDGET_CONTENT, {resource: resource}))
    }
  })
)(ResourceWidgetForm)

export {
  ResourceWidgetParameters
}
