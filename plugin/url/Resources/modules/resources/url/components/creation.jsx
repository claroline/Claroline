import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/resource/modals/creation/store'
import {constants} from '#/plugin/scorm/resources/scorm/constants'

const UrlForm = props =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_RESOURCE_PART}
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
          }, {
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
          }, {
            name: 'ratioList',
            type: 'choice',
            displayed: url => url && url.mode === 'iframe',
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
            displayed: url => url && url.mode === 'iframe',
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
  newNode: T.shape({
    name: T.string
  }),
  updateProp: T.func.isRequired
}

export {
  UrlForm
}
