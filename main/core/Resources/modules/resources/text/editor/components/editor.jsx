import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {FormData} from '#/main/app/content/form/containers/data'

const EditorComponent = (props) =>
  <FormData
    name="textForm"
    target={['apiv2_resource_text_update', {id: props.text.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        title: trans('general', {}, 'platform'),
        primary: true,
        fields: [
          {
            name: 'content',
            type: 'html',
            label: trans('text'),
            hideLabel: true,
            required: true,
            options: {
              minRows: 3
            }
          }
        ]
      }
    ]}
  />

EditorComponent.propTypes = {
  text: T.shape(
    TextTypes.propTypes
  ).isRequired
}

const Editor = connect(
  state => ({
    text: formSelect.data(formSelect.form(state, 'textForm'))
  })
)(EditorComponent)

export {
  Editor
}