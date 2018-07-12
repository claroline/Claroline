import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {actions as formActions} from '#/main/core/data/form/actions'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {constants} from '#/plugin/scorm/resources/scorm/constants'
import {Scorm as ScormType} from '#/plugin/scorm/resources/scorm/prop-types'
import {select} from '#/plugin/scorm/resources/scorm/selectors'

const EditorComponent = props =>
  <section className="resource-section">
    <h2>{trans('configuration')}</h2>
    <FormContainer
      level={3}
      name="scormForm"
      buttons={true}
      save={{
        type: 'callback',
        callback: () => props.saveForm(props.scorm.id)
      }}
      cancel={{
        type: 'link',
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
  </section>

EditorComponent.propTypes = {
  scorm: T.shape(ScormType.propTypes),
  updateProp: T.func.isRequired,
  saveForm: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    scorm: select.scorm(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('scormForm', propName, propValue))
    },
    saveForm(id) {
      dispatch(formActions.saveForm('scormForm', ['apiv2_scorm_update', {scorm: id}]))
    }
  })
)(EditorComponent)

export {
  Editor
}