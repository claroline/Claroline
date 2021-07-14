import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {constants} from '#/plugin/scorm/resources/scorm/constants'
import {selectors} from '#/plugin/scorm/resources/scorm/store'

const EditorComponent = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME+'.scormForm'}
    buttons={true}
    target={(scorm) => ['apiv2_scorm_update', {scorm: scorm.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'version',
            label: trans('version'),
            type: 'translation',
            readOnly: true,
            options: {
              domain: 'scorm'
            }
          }
        ]
      }, {
        id: 'display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'ratioList',
            type: 'choice',
            label: trans('display_ratio_list'),
            options: {
              multiple: false,
              condensed: false,
              choices: constants.DISPLAY_RATIO_LIST
            },
            onChange: (ratio) => props.updateProp('ratio', parseFloat(ratio))
          }, {
            name: 'ratio',
            type: 'number',
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
  updateProp: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.scormForm', propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}