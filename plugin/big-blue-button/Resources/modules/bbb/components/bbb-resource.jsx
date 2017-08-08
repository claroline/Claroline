import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {Route, Switch, withRouter} from 'react-router-dom'
import {Resource as ResourceContainer} from '#/main/core/layout/resource/containers/resource.jsx'
import {trans} from '#/main/core/translation'
import {actions} from '../actions'
import {BBBContent} from './bbb-content.jsx'
import {BBBConfig} from './bbb-config.jsx'

const BBBResource = props =>
  <ResourceContainer
    editor={{
      opened: '/edit' === props.location.pathname,
      open: '#/edit',
      save: {
        disabled: false,
        action: props.validateForm
      }
    }}
    customActions={customActions(props)}
  >
    <Switch>
      <Route path="/" component={BBBContent} exact={true} />
      <Route path="/edit" component={BBBConfig} />
    </Switch>
  </ResourceContainer>

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

function mapStateToProps(state) {
  return {
    canEdit: state.canEdit
  }
}

function mapDispatchToProps(dispatch) {
  return {
    validateForm: () => dispatch(actions.validateResourceForm()),
    endBBB: () => dispatch(actions.endBBB())
  }
}

const ConnectedBBBResource = withRouter(connect(mapStateToProps, mapDispatchToProps)(BBBResource))

export {ConnectedBBBResource as BBBResource}