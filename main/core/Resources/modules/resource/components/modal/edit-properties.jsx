import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'

import {trans}     from '#/main/core/translation'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form.jsx'

import {t_res}     from '#/main/core/resource/translation'
import {constants} from '#/main/core/resource/constants'

const MODAL_RESOURCE_PROPERTIES = 'MODAL_RESOURCE_PROPERTIES'

class EditPropertiesModal extends Component {
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
        title={t_res('edit-properties')}
        data={this.state.resourceNode}
        save={this.props.save}
        onChange={this.onChange}
        fadeModal={this.props.fadeModal}
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
                label: t_res('resource_description'),
                type: 'string',
                options: {
                  long: true
                }
              }, {
                name: 'meta.published',
                label: t_res('resource_not_published'),
                type: 'boolean',
                options: {
                  labelChecked: t_res('resource_published')
                }
              }, {
                name: 'meta.portal',
                label: t_res('resource_portal_not_published'),
                type: 'boolean',
                options: {
                  labelChecked: t_res('resource_portal_published')
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
                label: t_res('resource_showIcon'),
                help: t_res('resource_showIcon_help'),
                type: 'boolean'
              }, {
                name: 'display.fullscreen',
                label: t_res('resource_fullscreen'),
                type: 'boolean'
              }, {
                name: 'display.closable',
                label: t_res('resource_closable'),
                type: 'boolean'
              }, {
                name: 'display.closeTarget',
                label: t_res('resource_close_target'),
                type: 'enum',
                required: true,
                options: {
                  noEmpty: true,
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
                label: t_res('resource_access_code'),
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
                label: t_res('resource_access_ips'),
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
                      placeholder: t_res('resource_no_allowed_ip'),
                      multiple: true
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-copyright',
            title: t_res('resource_authors_license'),
            fields: [
              {
                name: 'meta.authors',
                label: t_res('resource_authors'),
                type: 'string'
              }, {
                name: 'meta.license',
                label: t_res('resource_license'),
                type: 'string'
              }
            ]
          }
        ]}
      />
    )
  }
}

EditPropertiesModal.propTypes = {
  resourceNode: T.object,
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

export {
  MODAL_RESOURCE_PROPERTIES,
  EditPropertiesModal
}
