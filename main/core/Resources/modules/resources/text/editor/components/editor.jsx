import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

const EditorComponent = () =>
  <section className="resource-section">
    <h2 className="h-first">{trans('configuration')}</h2>
    <FormContainer
      level={3}
      name="textForm"
      sections={[
        {
          id: 'general',
          title: trans('general', {}, 'platform'),
          primary: true,
          fields: [
            {
              name: 'content',
              type: 'html',
              label: trans('text'),
              options: {
                minRows: 3
              }
            }
          ]
        }
      ]}
    />
  </section>

EditorComponent.propTypes = {
  text: T.shape(TextTypes.propTypes).isRequired
}

const Editor = connect(
  state => ({
    text: formSelect.data(formSelect.form(state, 'textForm'))
  })
)(EditorComponent)

export {
  Editor
}