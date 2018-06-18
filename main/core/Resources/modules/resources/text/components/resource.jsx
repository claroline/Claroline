import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {actions as formActions} from '#/main/core/data/form/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {Text as TextTypes} from '#/main/core/resources/text/prop-types'

import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'
import {Player} from '#/main/core/resources/text/player/components/player.jsx'
import {Editor} from '#/main/core/resources/text/editor/components/editor.jsx'

const Resource = props =>
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
        type: 'link',
        icon: 'fa fa-fw fa-home',
        label: trans('home', {}, 'platform'),
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
  saveEnabled: T.bool.isRequired,
  resetForm: T.func.isRequired,
  saveForm: T.func.isRequired
}

const TextResource = connect(
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
  TextResource
}