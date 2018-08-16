import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'
import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/components/editor'

const Resource = props =>
  <ResourcePage>
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
  </ResourcePage>

Resource.propTypes = {
  text: T.shape(TextTypes.propTypes).isRequired,
  resetForm: T.func.isRequired
}

const FileResource = connect(
  state => ({
    text: state.text
  }),
  (dispatch) => ({
    resetForm: (formData) => dispatch(formActions.resetForm('textForm', formData))
  })
)(Resource)

export {
  FileResource
}