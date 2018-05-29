import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'

import {trans}     from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form'

import {constants} from '#/main/core/resource/constants'

class ParametersModal extends Component {
  constructor(props) {
    super(props)

    // We locally manage a copy of current node to be able
    // to display fields conditionality.
    this.state = {
      resourceNode: props.resourceNode ? cloneDeep(props.resourceNode) : {}
    }

    this.updateProp = this.updateProp.bind(this)
    this.onChange   = this.onChange.bind(this)
  }

  updateProp(propName, propValue) {
    const newNode = cloneDeep(this.state.resourceNode)

    set(newNode, propName, propValue)

    this.onChange(newNode)
  }

  onChange(newNode) {
    this.setState({
      resourceNode: newNode
    })
  }

  render() {
    return (
      <DataFormModal
        {...this.props}
        icon="fa fa-fw fa-cog"
        title={trans('parameters')}
        data={this.state.resourceNode}
        onChange={this.onChange}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'name',
                label: trans('name'),
                type: 'string',
                required: true
              }
            ]
          }, {
            icon: 'fa fa-fw fa-info',
            title: trans('information'),
            fields: [
              {
                name: 'meta.description',
                label: trans('description'),
                type: 'string',
                options: {
                  long: true
                }
              }, {
                name: 'meta.published',
                label: trans('resource_not_published', {}, 'resource'),
                type: 'boolean',
                options: {
                  labelChecked: trans('resource_published', {}, 'resource')
                }
              }, {
                name: 'meta.portal',
                label: trans('resource_portal_not_published', {}, 'resource'),
                type: 'boolean',
                options: {
                  labelChecked: trans('resource_portal_published', {}, 'resource')
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'poster',
                label: trans('poster'),
                type: 'image',
                options: {
                  ratio: '3:1'
                }
              }, {
                name: 'display.showIcon',
                label: trans('resource_showIcon', {}, 'resource'),
                help: trans('resource_showIcon_help'),
                type: 'boolean'
              }, {
                name: 'display.fullscreen',
                label: trans('resource_fullscreen', {}, 'resource'),
                type: 'boolean'
              }, {
                name: 'display.closable',
                label: trans('resource_closable', {}, 'resource'),
                type: 'boolean'
              }, {
                name: 'display.closeTarget',
                label: trans('resource_close_target', {}, 'resource'),
                type: 'choice',
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.RESOURCE_CLOSE_TARGETS
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-key',
            title: trans('access_restrictions'),
            fields: [
              {
                name: 'restrictions.hidden',
                type: 'boolean',
                label: trans('restrict_hidden'),
                help: trans('restrict_hidden_help')
              }, {
                name: 'restrictions.enableDates',
                label: trans('restrict_by_dates'),
                type: 'boolean',
                calculated: (node) => node.restrictions.enableDates || 0 !== node.restrictions.dates.length,
                onChange: activated => {
                  if (!activated) {
                    this.updateProp('restrictions.dates', [])
                  }
                },
                linked: [
                  {
                    name: 'restrictions.dates',
                    type: 'date-range',
                    label: trans('access_dates'),
                    displayed: (node) => node.restrictions.enableDates || 0 !== node.restrictions.dates.length,
                    required: true,
                    options: {
                      time: true
                    }
                  }
                ]
              }, {
                name: 'restrictions.enableCode',
                label: trans('resource_access_code', {}, 'resource'),
                type: 'boolean',
                calculated: (node) => node.restrictions.enableCode || !!node.restrictions.code,
                onChange: activated => {
                  if (!activated) {
                    this.updateProp('restrictions.code', null)
                  }
                },
                linked: [
                  {
                    name: 'restrictions.code',
                    label: trans('access_code'),
                    displayed: (node) => node.restrictions.enableCode || !!node.restrictions.code,
                    type: 'password',
                    required: true
                  }
                ]
              }, {
                name: 'restrictions.enableIps',
                label: trans('resource_access_ips', {}, 'resource'),
                type: 'boolean',
                calculated: (node) => node.restrictions.enableIps || 0 !== node.restrictions.allowedIps.length,
                onChange: activated => {
                  if (!activated) {
                    this.updateProp('restrictions.ips', [])
                  }
                },
                linked: [
                  {
                    name: 'restrictions.allowedIps',
                    label: trans('resource_allowed_ip'),
                    type: 'ip',
                    required: true,
                    displayed: (node) => node.restrictions.enableIps || 0 !== node.restrictions.allowedIps.length,
                    options: {
                      placeholder: trans('resource_no_allowed_ip', {}, 'resource'),
                      multiple: true
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-copyright',
            title: trans('authors_license'),
            fields: [
              {
                name: 'meta.authors',
                label: trans('authors'),
                type: 'string'
              }, {
                name: 'meta.license',
                label: trans('license'),
                type: 'string'
              }
            ]
          }
        ]}
      />
    )
  }
}

ParametersModal.propTypes = {
  resourceNode: T.object,
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

export {
  ParametersModal
}
