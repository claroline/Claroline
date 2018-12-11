import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store/actions'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {trans} from '#/main/app/intl/translation'

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
      target: '/',
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
            name: 'ratioList',
            type: 'choice',
            label: trans('display_ratio_list', {}, 'scorm'),
            options: {
              multiple: false,
              condensed: false,
              choices: constants.DISPLAY_RATIO_LIST
            },
            onChange: (ratio) => props.updateProp('ratio', parseFloat(ratio))
          }, {
            name: 'ratio',
            type: 'number',
            label: trans('display_ratio', {}, 'scorm'),
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
  updateProp: T.func.isRequired
}

const Editor = connect(
  null,
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.scormForm', propName, propValue))
    }
  })
)(EditorComponent)

export {
  Editor
}