import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {getTool} from '#/main/core/tool/utils'
import {selectors} from '#/main/core/tool/modals/parameters/store'

class ParametersModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      customForm: null
    }
  }

  componentDidMount() {
    getTool(this.props.toolName, this.props.currentContext.type).then((module) => {
      let parametersComponent = null
      if (module.default && module.default.parameters) {
        parametersComponent = module.default.parameters
      }

      this.setState({customForm: parametersComponent})
    })
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'toolName', 'currentContext', 'data', 'saveEnabled', 'onSave', 'save', 'reset')}
        icon="fa fa-fw fa-cog"
        title={trans('parameters')}
        subtitle={trans(this.props.toolName, {}, 'tools')}
        onEntering={() => this.props.reset(this.props.data)}
        size="lg"
      >
        <FormData
          flush={true}
          name={selectors.STORE_NAME}
          definition={[
            {
              title: trans('custom'),
              primary: true,
              displayed: !!this.state.customForm,
              fill: true,
              render: () => createElement(this.state.customForm)
            }, {
              icon: 'fa fa-fw fa-desktop',
              title: trans('display_parameters'),
              fields: [
                {
                  name: 'poster',
                  label: trans('poster'),
                  type: 'image'
                }, /*{
                  name: 'thumbnail',
                  label: trans('thumbnail'),
                  type: 'image'
                }, */{
                  name: 'display.order',
                  type: 'number',
                  label: trans('order'),
                  options: {
                    min: 0
                  }
                }, {
                  name: 'display.showIcon',
                  label: trans('resource_showIcon', {}, 'resource'),
                  type: 'boolean'
                }, /*{
                  name: 'display.fullscreen',
                  label: trans('resource_fullscreen', {}, 'resource'),
                  type: 'boolean'
                }, {
                  name: 'restrictions.hidden',
                  type: 'boolean',
                  label: trans('restrict_hidden')
                }*/
              ]
            }
          ]}
        >
          <Button
            className="modal-btn"
            variant="btn"
            size="lg"
            type={CALLBACK_BUTTON}
            htmlType="submit"
            primary={true}
            label={trans('save', {}, 'actions')}
            disabled={!this.props.saveEnabled}
            callback={() => {
              this.props.save(this.props.toolName, this.props.currentContext, this.props.onSave)
              this.props.fadeModal()
            }}
          />
        </FormData>
      </Modal>
    )
  }
}

ParametersModal.propTypes = {
  toolName: T.string.isRequired,
  data: T.object,
  currentContext: T.object.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  onSave: T.func,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ParametersModal
}
