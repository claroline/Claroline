import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {constants} from '#/plugin/scorm/resources/scorm/constants'
import {selectors} from '#/plugin/url/resources/url/editor/store'

const Editor = props =>
  <FormData
    level={5}
    name={selectors.FORM_NAME}
    target={['apiv2_url_update', {id: props.url.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('url'),
        primary: true,
        fields: [
          {
            name: 'raw',
            label: trans('url', {}, 'url'),
            type: 'url',
            required: true
          }, {
            name: 'mode',
            label: trans('mode'),
            help: 'iframe' === props.url.mode ? trans('https_iframe_help', {}, 'url') : undefined,
            type: 'choice',
            options: {
              multiple: false,
              condensed: true,
              choices: {
                iframe: trans('iframe_desc', {}, 'url'),
                redirect: trans('redirect_desc', {}, 'url'),
                tab: trans('tab_desc', {}, 'url')
              }
            },
            linked: [
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
                displayed: (url) => url.mode === 'iframe',
                label: trans('display_ratio'),
                options: {
                  min: 0,
                  unit: '%'
                },
                onChange: () => props.updateProp('ratioList', null)
              }
            ]
          }
        ]
      }
    ]}
  >
    <FormSections level={3}>
      <FormSection
        icon="fa fa-fw fa-exchange-alt"
        title={trans('parameters')}
      >
        <div className="alert alert-info">
          {trans('placeholders_info', {}, 'template')}
        </div>

        <table className="table table-bordered table-striped table-hover">
          <thead>
            <tr>
              <th>{trans('parameter')}</th>
              <th>{trans('description')}</th>
            </tr>
          </thead>

          <tbody>
            {props.availablePlaceholders.map((placeholder) =>
              <tr key={placeholder}>
                <td>{`%${placeholder}%`}</td>
                <td>{trans(`${placeholder}_desc`, {}, 'template')}</td>
              </tr>
            )}
          </tbody>
        </table>
      </FormSection>
    </FormSections>
  </FormData>

Editor.propTypes = {
  path: T.string.isRequired,
  url: T.shape({
    id: T.number.isRequired,
    mode: T.string
  }).isRequired,
  availablePlaceholders: T.arrayOf(T.string),
  updateProp: T.func.isRequired
}

export {
  Editor
}
