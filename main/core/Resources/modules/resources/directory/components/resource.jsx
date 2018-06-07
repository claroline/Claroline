import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as formActions} from '#/main/core/data/form/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/components/editor'

const Resource = props =>
  <ResourcePageContainer
    toolbar="create | edit edit-rights publish unpublish | fullscreen more"
    editor={{
      path: '/edit',
      save: {
        disabled: !props.saveEnabled,
        action: () => props.saveForm(props.text.id)
      }
    }}
  >
    <RoutedPageContent
      headerSpacer={true}
      routes={[
        {
          path: '/',
          exact: true,
          component: Player
        }, {
          path: '/edit',
          component: Editor,
          onEnter: () => props.resetForm(props.text)
        }
      ]}
    />
  </ResourcePageContainer>

Resource.propTypes = {
  text: T.shape(TextTypes.propTypes).isRequired,
  saveEnabled: T.bool.isRequired,
  resetForm: T.func.isRequired,
  saveForm: T.func.isRequired
}

const DirectoryResource = connect(
  state => ({
    text: state.text,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'textForm'))
  }),
  (dispatch) => ({
    resetForm: (formData) => dispatch(formActions.resetForm('textForm', formData)),
    saveForm: (id) => dispatch(formActions.saveForm('textForm', ['apiv2_resource_text_update', {id: id}]))
  })
)(Resource)

export {
  DirectoryResource
}