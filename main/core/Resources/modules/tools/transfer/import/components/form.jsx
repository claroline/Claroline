import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/tools/transfer/store'
import {Logs} from '#/main/core/tools/transfer/log/components/logs'

import {ImportExplanation} from '#/main/core/tools/transfer/import/components/explanation'
import {ImportSamples} from '#/main/core/tools/transfer/import/components/samples'

class ImportForm extends Component {
  constructor(props) {
    super(props)

    this.currentLogId = this.generateLogId()
    this.state = {
      currentSection: 'format'
    }
  }

  generateLogId() {
    const log = Math.random().toString(36).substring(7)
    this.currentLogId = log

    return log
  }

  getLogId() {
    return this.currentLogId
  }

  render() {
    const props = this.props

    const entity = props.match.params.entity
    const action = props.match.params.action
    const choices = {}

    Object.keys(props.explanation[entity]).reduce((o, key) => Object.assign(o, {[entity + '_' + key]: trans(key, {}, 'transfer')}), choices)

    const defaultFields = [
      {
        name: 'action',
        type: 'choice',
        label: trans('action'),
        onChange: (value) => {
          let action = ''
          if (value) {
            action = value.substring(value.indexOf('_') + 1)
          }

          props.history.push(`${this.props.path}/import/${entity}/${action}`)
          props.resetLog()
        },
        required: true,
        options: {
          noEmpty: false,
          condensed: true,
          choices: choices
        }
      }, {
        name: 'file',
        type: 'file',
        label: trans('file'),
        required: true,
        options: {
          uploadUrl: ['apiv2_transfer_upload_file', {workspace: props.workspace && props.workspace.id ? props.workspace.id : 0}]
        }
      }, {
        name: 'format',
        type: 'choice',
        label: trans('format'),
        required: true,
        options: {
          noEmpty: true,
          choices: {
            csv: trans('csv')
          }
        },
        linked: [
          {
            name: 'header',
            type: 'boolean',
            label: trans('csv_header', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => true
          }, {
            name: 'rowDelimiter',
            type: 'string',
            label: trans('row_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => '\\n'
          }, {
            name: 'columnDelimiter',
            type: 'string',
            label: trans('col_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => ';'
          }, {
            name: 'arrayDelimiter',
            type: 'string',
            label: trans('list_delimiter', {}, 'transfer'),
            required: true,
            disabled: true,
            calculated: () => ','
          }
        ]
      }
    ]

    const explanationAction = props.explanation[entity][action]
    const additionalFields = explanationAction ? explanationAction.fields || []: []

    return (
      <FormData
        level={2}
        className="component-container"
        name={selectors.STORE_NAME + '.import'}
        title={trans(entity, {}, 'transfer')}
        target={['apiv2_transfer_start', {
          log: this.getLogId(),
          workspace: props.workspace && props.workspace.id ? props.workspace.id : null
        }]}
        buttons={true}
        save={{
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-upload',
          label: trans('import', {}, 'actions'),
          callback: () => {
            const logName = this.getLogId()
            const refresher = setInterval(() => {
              this.props.loadLog(logName)
              if (this.props.logs && this.props.logs.total !== undefined && this.props.logs.processed === this.props.logs.total) {
                clearInterval(refresher)
              }
            }, 2000)

            this.generateLogId()

            this.setState({currentSection: 'log'})
          }
        }}
        cancel={{
          type: LINK_BUTTON,
          target: `${this.props.path}/import`,
          exact: true
        }}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: defaultFields.concat(additionalFields)
          }
        ]}
      >
        {action &&
          <Fragment>
            <ul className="nav nav-tabs">
              <li>
                <Button
                  type={CALLBACK_BUTTON}
                  label={trans('format')}
                  callback={() => this.setState({currentSection: 'format'})}
                  active={'format' === this.state.currentSection}
                />
              </li>

              <li>
                <Button
                  type={CALLBACK_BUTTON}
                  label={trans('examples', {}, 'transfer')}
                  callback={() => this.setState({currentSection: 'samples'})}
                  active={'samples' === this.state.currentSection}
                />
              </li>

              <li>
                <Button
                  type={CALLBACK_BUTTON}
                  label={trans('log')}
                  callback={() => this.setState({currentSection: 'log'})}
                  active={'log' === this.state.currentSection}
                />
              </li>
            </ul>

            {'format' === this.state.currentSection &&
              <ImportExplanation schema={get(this.props.explanation, entity+'.'+action, {})} />
            }

            {'samples' === this.state.currentSection &&
              <ImportSamples
                format="csv"
                entity={entity}
                action={action}
                samples={get(this.props.samples, entity+'.'+action, [])}
              />
            }

            {'log' === this.state.currentSection &&
              <Logs />
            }
          </Fragment>
        }
      </FormData>
    )
  }
}

ImportForm.propTypes = {
  path: T.string.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  match: T.shape({
    params: T.object.isRequired
  }).isRequired,
  explanation: T.object.isRequired,
  samples: T.object.isRequired,
  logs: T.object,
  workspace: T.object,

  updateProp: T.func.isRequired,
  resetLog: T.func.isRequired,
  loadLog: T.func.isRequired
}

export {
  ImportForm
}