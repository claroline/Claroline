import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'
import uniq from 'lodash/uniq'
import isUndefined from 'lodash/isUndefined'

import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Form} from '#/main/app/content/form/containers/form'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/community/tools/community/role/modals/rights/store'

const ToolRights = (props) => {
  const allPerms = uniq(Object.keys(props.rights)
    .reduce((accumulator, current) => accumulator.concat(
      Object.keys(props.rights[current])
    ), []))

  return (
    <table className="table table-striped table-hover content-rights-advanced">
      <thead>
        <tr>
          <th scope="col">{trans('tool')}</th>

          {allPerms.map(permission =>
            <th key={`${permission}-header`} scope="col">
              <div className="permission-name-container">
                <span className="permission-name">{trans(permission, {}, 'actions')}</span>
              </div>
            </th>
          )}
        </tr>
      </thead>

      <tbody>
        {Object.keys(props.rights).map((toolName) => {
          return (
            <tr key={toolName}>
              <th scope="row">{trans(toolName, {}, 'tools')}</th>
              {allPerms.map(toolPerm => (
                <td
                  key={toolPerm}
                  className="checkbox-cell"
                >
                  {!isUndefined(props.rights[toolName][toolPerm]) &&
                    <input
                      type="checkbox"
                      checked={get(props.rights, `${toolName}.${toolPerm}`, false)}
                      onChange={() => props.update(toolName, toolPerm, !get(props.rights, `${toolName}.${toolPerm}`, false))}
                    />
                  }
                </td>
              ))}
            </tr>
          )
        })}
      </tbody>
    </table>
  )
}

const RightsModal = props =>
  <Modal
    {...omit(props, 'role', 'workspace', 'rights', 'formData', 'save', 'saveEnabled', 'updateRights', 'loadRights', 'onSave')}
    icon="fa fa-fw fa-lock"
    title={trans('rights')}
    subtitle={props.title}
    onEntering={() => props.loadRights(props.rights)}
  >
    <Form
      name={selectors.STORE_NAME}
    >
      <ToolRights
        rights={props.formData}
        update={props.updateRights}
      />

      <Button
        className="btn modal-btn"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        htmlType="submit"
        callback={() => {
          props.save(props.role, props.contextType, props.contextId, props.onSave)
          props.fadeModal()
        }}
      />
    </Form>
  </Modal>

RightsModal.propTypes = {
  title: T.string,
  role: T.object.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  rights: T.object.isRequired,
  saveEnabled: T.bool.isRequired,
  formData: T.object,
  save: T.func.isRequired,
  onSave: T.func,
  updateRights: T.func.isRequired,
  loadRights: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  RightsModal
}
