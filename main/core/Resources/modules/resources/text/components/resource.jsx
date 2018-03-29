import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as resourceSelect} from '#/main/core/resource/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'
import {Player} from '#/main/core/resources/text/player/components/player.jsx'
import {Editor} from '#/main/core/resources/text/editor/components/editor.jsx'

const Resource = props => {
  const routes = [
    {
      path: '/play',
      component: Player
    }, {
      path: '/edit',
      component: Editor,
      canEnter: () => props.canEdit,
      onEnter: () => props.resetForm(props.text)
    }
  ]
  const redirect = [{
    from: '/',
    to: '/play',
    exact: true
  }]

  return (
    <ResourcePageContainer
      editor={{
        path: '/edit',
        save: {
          disabled: !props.saveEnabled,
          action: () => props.saveForm(props.text.id)
        }
      }}
      customActions={[
        {
          icon: 'fa fa-fw fa-home',
          label: trans('show_overview'),
          displayed: props.canEdit,
          action: '#/'
        }
      ]}
    >
      <RoutedPageContent
        headerSpacer={false}
        redirect={redirect}
        routes={routes}
      />
    </ResourcePageContainer>
  )
}

Resource.propTypes = {
  text: T.shape(TextTypes.propTypes).isRequired,
  resource: T.shape({
    id: T.string.isRequired,
    autoId: T.number.isRequired
  }).isRequired,
  canEdit: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  resetForm: T.func.isRequired,
  saveForm: T.func.isRequired
}

const TextResource = connect(
  state => ({
    text: state.text,
    resource: state.resourceNode,
    canEdit: resourceSelect.editable(state),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'textForm'))
  }),
  (dispatch) => ({
    resetForm: (formData) => dispatch(formActions.resetForm('textForm', formData)),
    saveForm: (id) => dispatch(formActions.saveForm('textForm', ['apiv2_resource_text_update', {id: id}]))
  })
)(Resource)

export {
  TextResource
}