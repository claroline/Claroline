import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {constants} from '#/plugin/lti/resources/lti/constants'
import {selectors} from '#/plugin/lti/resources/lti/store'
import {LtiApp as LtiAppType} from '#/plugin/lti/resources/lti/prop-types'

const EditorComponent = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME+'.ltiResourceForm'}
    buttons={true}
    target={(ltiResource) => ['apiv2_ltiresource_update', {id: ltiResource.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        id: 'display',
        icon: 'fa fa-fw fa-th-list',
        title: trans('display'),
        primary: true,
        fields: [
          {
            name: 'ltiApp.id',
            type: 'choice',
            label: trans('choice_app', {}, 'lti'),
            required: true,
            options: {
              multiple: false,
              condensed: true,
              choices: props.ltiApps.reduce((acc, app) => {
                acc[app.id] = app.title

                return acc
              }, {})
            }
          }, {
            name: 'openInNewTab',
            type: 'boolean',
            label: trans('open_application_in_a_new_tab', {}, 'lti')
          }, {
            name: 'ratioList',
            type: 'choice',
            displayed: (ltiResource) => ltiResource && !ltiResource.openInNewTab,
            label: trans('display_ratio_list'),
            options: {
              multiple: false,
              condensed: false,
              choices: constants.DISPLAY_RATIO_LIST
            },
            onChange: (ratio) => {
              props.updateProp('ratio', parseFloat(ratio))
            }
          }, {
            name: 'ratio',
            type: 'number',
            displayed: (ltiResource) => ltiResource && !ltiResource.openInNewTab,
            label: trans('display_ratio'),
            options: {
              min: 0,
              unit: '%'
            },
            onChange: () => props.updateProp('ratioList', null)
          }
        ]
      }
    ]}
  />

EditorComponent.propTypes = {
  path: T.string.isRequired,
  ltiApps: T.arrayOf(T.shape(LtiAppType.propTypes)).isRequired,
  updateProp: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    ltiApps: selectors.ltiApps(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.ltiResourceForm', propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}