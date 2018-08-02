import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {Player} from '#/main/core/resources/text/player/components/player'
import {Editor} from '#/main/core/resources/text/editor/components/editor'

const Resource = props =>
  <ResourcePageContainer
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('home'),
        target: '/',
        exact: true
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={true}
      routes={[
        {
          path: '/',
          component: Player,
          exact: true
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
  resetForm: T.func.isRequired
}

const TextResource = connect(
  state => ({
    text: state.text
  }),
  (dispatch) => ({
    resetForm: (formData) => dispatch(formActions.resetForm('textForm', formData))
  })
)(Resource)

export {
  TextResource
}