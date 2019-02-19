import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/plugin/url/resources/url/editor/store'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants} from '#/plugin/scorm/resources/scorm/constants'
import {actions as formActions} from '#/main/app/content/form/store/actions'

const UrlForm = props =>
  <FormData
    level={5}
    name={selectors.FORM_NAME}
    target={['apiv2_url_update', {id: props.url.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/',
      exact: true
    }}
    sections={[
      {
        title: trans('url'),
        primary: true,
        fields: [
          {
            name: 'url',
            label: trans('url', {}, 'url'),
            type: 'url',
            required: true
          },
          {
            name: 'mode',
            label: trans('mode'),
            type: 'choice',
            required: true,
            options: {
              multiple: false,
              condensed: true,
              choices: {
                'iframe': trans('iframe_desc', {}, 'url'),
                'redirect': trans('redirect_desc', {}, 'url'),
                'tab': trans('tab_desc', {}, 'url')
              }
            }
          },
          {
            name: 'ratioList',
            type: 'choice',
            displayed: url => url.mode === 'iframe',
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
            displayed: url => url.mode === 'iframe',
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

UrlForm.propTypes = {
  url: T.shape({
    'id': T.number.isRequired
  }).isRequired,
  updateProp: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    url: selectors.url(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, propName, propValue))
    }
  })
)(UrlForm)

export {
  Editor
}
