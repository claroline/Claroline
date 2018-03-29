import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

import {actions} from '../actions'
import {BBBContent} from './bbb-content.jsx'
import {BBBConfig} from './bbb-config.jsx'

const BBBResource = props =>
  <ResourcePageContainer
    editor={{
      path: '/edit',
      save: {
        disabled: false,
        action: props.validateForm
      }
    }}
    customActions={customActions(props)}
  >
    <RoutedPageContent
      routes={[
        {
          path: '/',
          exact: true,
          component: BBBContent
        }, {
          path: '/edit',
          component: BBBConfig
        }
      ]}
    />
  </ResourcePageContainer>

BBBResource.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }).isRequired,
  validateForm: T.func,
  endBBB: T.func
}

function customActions(props) {
  const actions = []

  actions.push({
    icon: 'fa fa-fw fa-home',
    label: trans('claroline_big_blue_button', {}, 'resource'),
    action: '#/'
  })

  if (props.canEdit) {
    actions.push({
      icon: 'fa fa-fw fa-stop-circle',
      label: trans('bbb_end', {}, 'bbb'),
      action: props.endBBB
    })
  }

  return actions
}

const ConnectedBBBResource = connect(
  state => ({
    canEdit: state.canEdit
  }),
  dispatch => ({
    validateForm: () => dispatch(actions.validateResourceForm()),
    endBBB: () => dispatch(actions.endBBB())
  })
)(BBBResource)

export {
  ConnectedBBBResource as BBBResource
}
