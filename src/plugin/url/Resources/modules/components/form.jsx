import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {Url as UrlTypes} from '#/plugin/url/prop-types'
import {constants} from '#/plugin/scorm/resources/scorm/constants'

class UrlForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      placeholders: []
    }
  }

  componentDidMount() {
    fetch(url(['apiv2_url_placeholders']), {
      method: 'GET' ,
      credentials: 'include'
    })
      .then(response => response.json())
      .then(response => this.setState({placeholders: response}))
  }

  render() {
    return (
      <FormData
        {...omit(this.props)}
        name={this.props.name}
        sections={[
          {
            title: trans('url'),
            primary: true,
            fields: [
              {
                name: 'raw',
                label: trans('url'),
                type: 'url',
                required: true
              }, {
                name: 'mode',
                label: trans('mode'),
                help: 'iframe' === get(this.props.url, 'mode') ? trans('https_iframe_help', {}, 'url') : undefined,
                type: 'choice',
                required: true,
                options: {
                  multiple: false,
                  choices: {
                    button: trans('button_desc', {}, 'url'),
                    iframe: trans('iframe_desc', {}, 'url'),
                    redirect: trans('redirect_desc', {}, 'url'),
                    tab: trans('tab_desc', {}, 'url')
                  }
                },
                linked: [
                  {
                    name: 'ratioList',
                    type: 'choice',
                    displayed: 'iframe' === get(this.props.url, 'mode'),
                    label: trans('display_ratio_list'),
                    options: {
                      multiple: false,
                      condensed: false,
                      choices: constants.DISPLAY_RATIO_LIST
                    },
                    onChange: (ratio) => this.props.updateProp('ratio', parseFloat(ratio))
                  }, {
                    name: 'ratio',
                    type: 'number',
                    displayed: 'iframe' === get(this.props.url, 'mode'),
                    label: trans('display_ratio'),
                    options: {
                      min: 0,
                      unit: '%'
                    },
                    onChange: () => this.props.updateProp('ratioList', null)
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
                {this.state.placeholders.map((placeholder) =>
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
    )
  }
}

UrlForm.propTypes = {
  name: T.string.isRequired,
  url: T.shape(
    UrlTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired
}

export {
  UrlForm
}
