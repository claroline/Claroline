import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {constants} from '#/main/app/content/list/constants'

const ConfigurationForm = props =>
  <FormData
    embedded={true}
    name={props.name}
    dataPart="list"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [

        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [

        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('Pagination'),
        fields: [
          {
            name: 'pagination.enabled',
            label: trans('enable_pagination'),
            type: 'boolean',
            linked: [
              {
                name: 'pagination.default',
                label: trans('enable_pagination'),
                type: 'choice',
                displayed: (config) => config.pagination.enabled,
                options: {
                  choices: constants.AVAILABLE_PAGE_SIZES.reduce(
                    (acc, current) => Object.assign(acc, {[current]: -1 !== current ? current : trans('all')}), {}
                  ),
                  noEmpty: true
                }
              }
            ]
          }, {

          }
        ]
      }
    ]}
  />

ConfigurationForm.propTypes = {
  name: T.string.isRequired
}

export {
  ConfigurationForm
}
